<?php
/**
 * فئة الفاتورة
 * Invoice class
 */

class Invoice {
    private $db;

    /**
     * إنشاء كائن الفاتورة
     * Create invoice object
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * إنشاء فاتورة جديدة
     * Create new invoice
     */
    public function create($orderId, $userId) {
        // التحقق من وجود الطلب
        // Check if order exists
        $order = new Order();
        $orderData = $order->getById($orderId);
        
        if (!$orderData) {
            throw new Exception(translate('order_not_found'));
        }

        // التحقق من عدم وجود فاتورة سابقة للطلب
        // Check if invoice already exists for this order
        $existingInvoice = $this->getByOrderId($orderId);
        if ($existingInvoice) {
            return $existingInvoice['id'];
        }

        // إنشاء رقم الفاتورة
        // Create invoice number
        $invoiceNumber = $this->generateInvoiceNumber();
        
        // حساب الضريبة والمبلغ الإجمالي
        // Calculate tax and total amount
        $amount = $orderData['total_amount'];
        $taxAmount = $amount * TAX_RATE;
        $totalAmount = $amount + $taxAmount;

        // إنشاء بيانات الفاتورة
        // Create invoice data
        $invoiceData = [
            'order_id' => $orderId,
            'user_id' => $userId,
            'invoice_number' => $invoiceNumber,
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'status' => INVOICE_STATUS_PAID,
            'due_date' => date('Y-m-d', strtotime('+7 days')),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // إدراج الفاتورة في قاعدة البيانات
        // Insert invoice into database
        $invoiceId = $this->db->insert('invoices', $invoiceData);

        // إنشاء ملف PDF للفاتورة
        // Create PDF file for invoice
        $pdfPath = $this->generatePDF($invoiceId);
        
        // تحديث مسار ملف PDF
        // Update PDF file path
        $this->db->update('invoices', ['pdf_path' => $pdfPath], 'id = :id', [':id' => $invoiceId]);

        // تسجيل نشاط إنشاء الفاتورة
        // Log invoice creation activity
        logActivity($userId, 'create_invoice', 'Created invoice: ' . $invoiceNumber);

        return $invoiceId;
    }

    /**
     * تحديث حالة الفاتورة
     * Update invoice status
     */
    public function updateStatus($invoiceId, $status) {
        // التحقق من وجود الفاتورة
        // Check if invoice exists
        $invoice = $this->getById($invoiceId);
        
        if (!$invoice) {
            throw new Exception(translate('invoice_not_found'));
        }

        // تحديث حالة الفاتورة
        // Update invoice status
        $result = $this->db->update('invoices', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $invoiceId]);

        // تسجيل نشاط تحديث حالة الفاتورة
        // Log invoice status update activity
        logActivity($_SESSION['user_id'], 'update_invoice_status', 'Updated invoice status: ' . $invoice['invoice_number'] . ' to ' . $status);

        return $result;
    }

    /**
     * الحصول على فاتورة بواسطة المعرف
     * Get invoice by ID
     */
    public function getById($invoiceId) {
        $invoice = $this->db->getRow("SELECT * FROM invoices WHERE id = :id", [':id' => $invoiceId]);
        
        if (!$invoice) {
            return false;
        }

        // الحصول على بيانات الطلب
        // Get order data
        $order = new Order();
        $invoice['order'] = $order->getById($invoice['order_id']);

        // الحصول على بيانات المستخدم
        // Get user data
        if ($invoice['user_id']) {
            $user = new User();
            $invoice['user'] = $user->getById($invoice['user_id']);
        } else {
            $invoice['user'] = null;
        }

        return $invoice;
    }

    /**
     * الحصول على فاتورة بواسطة رقم الفاتورة
     * Get invoice by invoice number
     */
    public function getByInvoiceNumber($invoiceNumber) {
        $invoice = $this->db->getRow("SELECT * FROM invoices WHERE invoice_number = :invoice_number", [':invoice_number' => $invoiceNumber]);
        
        if (!$invoice) {
            return false;
        }

        // الحصول على بيانات الطلب
        // Get order data
        $order = new Order();
        $invoice['order'] = $order->getById($invoice['order_id']);

        // الحصول على بيانات المستخدم
        // Get user data
        if ($invoice['user_id']) {
            $user = new User();
            $invoice['user'] = $user->getById($invoice['user_id']);
        } else {
            $invoice['user'] = null;
        }

        return $invoice;
    }

    /**
     * الحصول على فاتورة بواسطة معرف الطلب
     * Get invoice by order ID
     */
    public function getByOrderId($orderId) {
        $invoice = $this->db->getRow("SELECT * FROM invoices WHERE order_id = :order_id", [':order_id' => $orderId]);
        
        if (!$invoice) {
            return false;
        }

        // الحصول على بيانات الطلب
        // Get order data
        $order = new Order();
        $invoice['order'] = $order->getById($invoice['order_id']);

        // الحصول على بيانات المستخدم
        // Get user data
        if ($invoice['user_id']) {
            $user = new User();
            $invoice['user'] = $user->getById($invoice['user_id']);
        } else {
            $invoice['user'] = null;
        }

        return $invoice;
    }

    /**
     * الحصول على فواتير المستخدم
     * Get user invoices
     */
    public function getUserInvoices($userId, $page = 1, $perPage = ITEMS_PER_PAGE) {
        $where = "user_id = :user_id";
        $params = [':user_id' => $userId];

        $result = $this->db->getPaginated('invoices', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على بيانات الطلبات
        // Get orders data
        foreach ($result['data'] as &$invoice) {
            $order = new Order();
            $invoice['order'] = $order->getById($invoice['order_id']);
        }

        return $result;
    }

    /**
     * الحصول على جميع الفواتير
     * Get all invoices
     */
    public function getAllInvoices($page = 1, $perPage = ITEMS_PER_PAGE, $status = null) {
        $where = '';
        $params = [];

        if ($status !== null) {
            $where = "status = :status";
            $params[':status'] = $status;
        }

        $result = $this->db->getPaginated('invoices', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على بيانات الطلبات والمستخدمين
        // Get orders and users data
        foreach ($result['data'] as &$invoice) {
            $order = new Order();
            $invoice['order'] = $order->getById($invoice['order_id']);
            
            if ($invoice['user_id']) {
                $user = new User();
                $invoice['user'] = $user->getById($invoice['user_id']);
            } else {
                $invoice['user'] = null;
            }
        }

        return $result;
    }

    /**
     * البحث عن الفواتير
     * Search invoices
     */
    public function searchInvoices($query, $page = 1, $perPage = ITEMS_PER_PAGE) {
        $where = "invoice_number LIKE :query";
        $params = [':query' => "%{$query}%"];

        $result = $this->db->getPaginated('invoices', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على بيانات الطلبات والمستخدمين
        // Get orders and users data
        foreach ($result['data'] as &$invoice) {
            $order = new Order();
            $invoice['order'] = $order->getById($invoice['order_id']);
            
            if ($invoice['user_id']) {
                $user = new User();
                $invoice['user'] = $user->getById($invoice['user_id']);
            } else {
                $invoice['user'] = null;
            }
        }

        return $result;
    }

    /**
     * توليد رقم فاتورة فريد
     * Generate unique invoice number
     */
    private function generateInvoiceNumber() {
        $prefix = 'INV';
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        return $prefix . $timestamp . $random;
    }

    /**
     * إنشاء ملف PDF للفاتورة
     * Generate PDF file for invoice
     */
    private function generatePDF($invoiceId) {
        // الحصول على بيانات الفاتورة
        // Get invoice data
        $invoice = $this->getById($invoiceId);
        
        if (!$invoice) {
            throw new Exception(translate('invoice_not_found'));
        }

        // إنشاء اسم الملف
        // Create file name
        $fileName = 'invoice_' . $invoice['invoice_number'] . '.pdf';
        $filePath = 'invoices/' . $fileName;
        $fullPath = UPLOAD_DIR . $filePath;
        
        // التأكد من وجود المجلد
        // Make sure directory exists
        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        // إنشاء ملف PDF باستخدام TCPDF
        // Create PDF file using TCPDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // إعداد الملف
        // Setup file
        $pdf->SetCreator(SITE_NAME_EN);
        $pdf->SetAuthor(SITE_NAME_EN);
        $pdf->SetTitle('Invoice #' . $invoice['invoice_number']);
        $pdf->SetSubject('Invoice #' . $invoice['invoice_number']);
        $pdf->SetKeywords('Invoice, ' . SITE_NAME_EN);
        
        // إزالة الرأس والتذييل
        // Remove header and footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // تعيين الهوامش
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        
        // إضافة صفحة
        // Add page
        $pdf->AddPage();
        
        // إضافة محتوى الفاتورة
        // Add invoice content
        $html = $this->generateInvoiceHTML($invoice);
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // حفظ الملف
        // Save file
        $pdf->Output($fullPath, 'F');
        
        return $filePath;
    }

    /**
     * إنشاء HTML للفاتورة
     * Generate HTML for invoice
     */
    private function generateInvoiceHTML($invoice) {
        $order = $invoice['order'];
        $user = $invoice['user'];
        $items = $order['items'];
        
        $html = '
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12pt;
            }
            .invoice-header {
                text-align: center;
                margin-bottom: 20px;
            }
            .invoice-title {
                font-size: 24pt;
                font-weight: bold;
                margin-bottom: 10px;
            }
            .invoice-number {
                font-size: 14pt;
                margin-bottom: 20px;
            }
            .section {
                margin-bottom: 20px;
            }
            .section-title {
                font-size: 14pt;
                font-weight: bold;
                margin-bottom: 10px;
                border-bottom: 1px solid #ccc;
                padding-bottom: 5px;
            }
            .info-row {
                margin-bottom: 5px;
            }
            .info-label {
                font-weight: bold;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th {
                background-color: #f2f2f2;
                text-align: left;
                padding: 8px;
                border: 1px solid #ddd;
            }
            td {
                padding: 8px;
                border: 1px solid #ddd;
            }
            .total-row {
                font-weight: bold;
            }
            .footer {
                margin-top: 50px;
                text-align: center;
                font-size: 10pt;
                color: #666;
            }
        </style>
        
        <div class="invoice-header">
            <div class="invoice-title">' . SITE_NAME_EN . '</div>
            <div class="invoice-number">' . translate('invoice') . ' #' . $invoice['invoice_number'] . '</div>
        </div>
        
        <div class="section">
            <div class="section-title">' . translate('invoice_details') . '</div>
            <div class="info-row"><span class="info-label">' . translate('invoice_date') . ':</span> ' . formatDateTime($invoice['created_at'], 'Y-m-d') . '</div>
            <div class="info-row"><span class="info-label">' . translate('due_date') . ':</span> ' . formatDateTime($invoice['due_date'], 'Y-m-d') . '</div>
            <div class="info-row"><span class="info-label">' . translate('status') . ':</span> ' . translate($invoice['status']) . '</div>
        </div>
        
        <div class="section">
            <div class="section-title">' . translate('customer_details') . '</div>';
            
        if ($user) {
            $html .= '
            <div class="info-row"><span class="info-label">' . translate('name') . ':</span> ' . $user['first_name'] . ' ' . $user['last_name'] . '</div>
            <div class="info-row"><span class="info-label">' . translate('email') . ':</span> ' . $user['email'] . '</div>
            <div class="info-row"><span class="info-label">' . translate('phone') . ':</span> ' . $user['phone'] . '</div>
            <div class="info-row"><span class="info-label">' . translate('address') . ':</span> ' . $user['address'] . '</div>
            <div class="info-row"><span class="info-label">' . translate('city') . ':</span> ' . $user['city'] . '</div>
            <div class="info-row"><span class="info-label">' . translate('country') . ':</span> ' . $user['country'] . '</div>';
        } else {
            $html .= '<div class="info-row">' . translate('customer_not_found') . '</div>';
        }
        
        $html .= '
        </div>
        
        <div class="section">
            <div class="section-title">' . translate('order_details') . '</div>
            <div class="info-row"><span class="info-label">' . translate('order_number') . ':</span> ' . $order['order_number'] . '</div>
            <div class="info-row"><span class="info-label">' . translate('order_date') . ':</span> ' . formatDateTime($order['created_at'], 'Y-m-d') . '</div>
            <div class="info-row"><span class="info-label">' . translate('payment_method') . ':</span> ' . translate($order['payment_method']) . '</div>
        </div>
        
        <div class="section">
            <div class="section-title">' . translate('items') . '</div>
            <table>
                <tr>
                    <th>' . translate('item') . '</th>
                    <th>' . translate('quantity') . '</th>
                    <th>' . translate('price') . '</th>
                    <th>' . translate('total') . '</th>
                </tr>';
        
        foreach ($items as $item) {
            $itemName = '';
            switch ($item['item_type']) {
                case ITEM_TYPE_PRODUCT:
                    $itemName = $item['product']['name_' . getCurrentLanguage()];
                    break;
                case ITEM_TYPE_SERVICE:
                    $itemName = $item['service']['name_' . getCurrentLanguage()];
                    break;
                case ITEM_TYPE_CUSTOM_SERVICE:
                    $itemName = $item['custom_service']['name_' . getCurrentLanguage()];
                    break;
            }
            
            $html .= '
                <tr>
                    <td>' . $itemName . '</td>
                    <td>' . $item['quantity'] . '</td>
                    <td>' . formatPrice($item['price']) . '</td>
                    <td>' . formatPrice($item['total']) . '</td>
                </tr>';
        }
        
        $html .= '
                <tr>
                    <td colspan="3" align="right"><strong>' . translate('subtotal') . ':</strong></td>
                    <td>' . formatPrice($invoice['amount']) . '</td>
                </tr>
                <tr>
                    <td colspan="3" align="right"><strong>' . translate('tax') . ' (' . (TAX_RATE * 100) . '%):</strong></td>
                    <td>' . formatPrice($invoice['tax_amount']) . '</td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" align="right"><strong>' . translate('total') . ':</strong></td>
                    <td>' . formatPrice($invoice['total_amount']) . '</td>
                </tr>
            </table>
        </div>
        
        <div class="footer">
            <p>' . translate('thank_you_for_your_business') . '</p>
            <p>' . SITE_NAME_EN . ' - ' . translate('generated_on') . ' ' . date('Y-m-d H:i:s') . '</p>
        </div>';
        
        return $html;
    }

    /**
     * إرسال الفاتورة بالبريد الإلكتروني
     * Send invoice by email
     */
    public function sendByEmail($invoiceId) {
        // الحصول على بيانات الفاتورة
        // Get invoice data
        $invoice = $this->getById($invoiceId);
        
        if (!$invoice) {
            throw new Exception(translate('invoice_not_found'));
        }

        // التحقق من وجود المستخدم
        // Check if user exists
        if (!$invoice['user']) {
            throw new Exception(translate('user_not_found'));
        }

        // التحقق من وجود ملف PDF
        // Check if PDF file exists
        if (empty($invoice['pdf_path']) || !file_exists(UPLOAD_DIR . $invoice['pdf_path'])) {
            throw new Exception(translate('invoice_pdf_not_found'));
        }

        // إرسال البريد الإلكتروني
        // Send email
        // تنفيذ إرسال البريد الإلكتروني
        // Implement email sending
        // ...

        // تسجيل نشاط إرسال الفاتورة
        // Log invoice sending activity
        logActivity($_SESSION['user_id'], 'send_invoice', 'Sent invoice: ' . $invoice['invoice_number'] . ' to ' . $invoice['user']['email']);

        return true;
    }
}
