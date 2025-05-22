<?php
/**
 * قالب عرض تذكرة الدعم
 * Support Ticket View Template
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">تذكرة الدعم #<?php echo $ticket['id']; ?></h4>
                    <div>
                        <?php if ($ticket['status'] !== 'closed'): ?>
                            <a href="index.php?action=close&id=<?php echo $ticket['id']; ?>" class="btn btn-warning" onclick="return confirm('هل أنت متأكد من إغلاق هذه التذكرة؟')">
                                <i class="fa fa-times"></i> إغلاق التذكرة
                            </a>
                        <?php else: ?>
                            <a href="index.php?action=reopen&id=<?php echo $ticket['id']; ?>" class="btn btn-success">
                                <i class="fa fa-refresh"></i> إعادة فتح التذكرة
                            </a>
                        <?php endif; ?>
                        <a href="index.php?action=delete&id=<?php echo $ticket['id']; ?>" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذه التذكرة؟ سيتم حذف جميع الردود أيضاً.')">
                            <i class="fa fa-trash"></i> حذف التذكرة
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> العودة إلى القائمة
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- معلومات التذكرة -->
                    <!-- Ticket information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>الموضوع</th>
                                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                </tr>
                                <tr>
                                    <th>القسم</th>
                                    <td><?php echo htmlspecialchars($ticket['department']); ?></td>
                                </tr>
                                <tr>
                                    <th>الأولوية</th>
                                    <td>
                                        <?php
                                        $priorityClass = '';
                                        switch ($ticket['priority']) {
                                            case 'low':
                                                $priorityClass = 'text-success';
                                                $priorityText = 'منخفضة';
                                                break;
                                            case 'medium':
                                                $priorityClass = 'text-warning';
                                                $priorityText = 'متوسطة';
                                                break;
                                            case 'high':
                                                $priorityClass = 'text-danger';
                                                $priorityText = 'عالية';
                                                break;
                                            default:
                                                $priorityClass = 'text-secondary';
                                                $priorityText = $ticket['priority'];
                                        }
                                        ?>
                                        <span class="<?php echo $priorityClass; ?>"><?php echo $priorityText; ?></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>الحالة</th>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        switch ($ticket['status']) {
                                            case 'new':
                                                $statusClass = 'badge bg-info';
                                                $statusText = 'جديدة';
                                                break;
                                            case 'open':
                                                $statusClass = 'badge bg-primary';
                                                $statusText = 'مفتوحة';
                                                break;
                                            case 'read':
                                                $statusClass = 'badge bg-secondary';
                                                $statusText = 'مقروءة';
                                                break;
                                            case 'replied':
                                                $statusClass = 'badge bg-success';
                                                $statusText = 'تم الرد';
                                                break;
                                            case 'closed':
                                                $statusClass = 'badge bg-danger';
                                                $statusText = 'مغلقة';
                                                break;
                                            default:
                                                $statusClass = 'badge bg-secondary';
                                                $statusText = $ticket['status'];
                                        }
                                        ?>
                                        <span class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>تاريخ الإنشاء</th>
                                    <td><?php echo date('Y-m-d H:i', strtotime($ticket['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <th>آخر تحديث</th>
                                    <td><?php echo date('Y-m-d H:i', strtotime($ticket['updated_at'])); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- معلومات المستخدم -->
                    <!-- User information -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">معلومات المستخدم</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>الاسم:</strong> <?php echo htmlspecialchars($ticket['user_name']); ?></p>
                                            <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($ticket['user_email']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>رقم المستخدم:</strong> <?php echo $ticket['user_id']; ?></p>
                                            <p><strong>رقم الهاتف:</strong> <?php echo htmlspecialchars($ticket['phone'] ?? 'غير متوفر'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- محتوى التذكرة -->
                    <!-- Ticket content -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">محتوى التذكرة</h5>
                                </div>
                                <div class="card-body">
                                    <div class="ticket-content">
                                        <?php echo nl2br(htmlspecialchars($ticket['content'])); ?>
                                    </div>
                                    
                                    <?php if (!empty($ticket['attachments'])): ?>
                                        <hr>
                                        <h6>المرفقات:</h6>
                                        <div class="attachments">
                                            <?php
                                            $attachments = json_decode($ticket['attachments'], true);
                                            if (is_array($attachments)) {
                                                foreach ($attachments as $attachment) {
                                                    echo '<div class="attachment">';
                                                    echo '<a href="' . SITE_URL . '/uploads/support/' . $attachment . '" target="_blank">';
                                                    echo '<i class="fa fa-file"></i> ' . $attachment;
                                                    echo '</a>';
                                                    echo '</div>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- الردود -->
                    <!-- Replies -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">الردود (<?php echo count($replies); ?>)</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($replies)): ?>
                                        <div class="alert alert-info">
                                            لا توجد ردود على هذه التذكرة حتى الآن.
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($replies as $reply): ?>
                                            <div class="reply <?php echo $reply['user_role'] === 'admin' ? 'admin-reply' : 'user-reply'; ?> mb-4">
                                                <div class="reply-header d-flex justify-content-between">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($reply['user_name']); ?></strong>
                                                        <span class="text-muted">(<?php echo $reply['user_role'] === 'admin' ? 'مدير' : 'مستخدم'; ?>)</span>
                                                        <?php if ($reply['is_private']): ?>
                                                            <span class="badge bg-warning">خاص</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="reply-date">
                                                        <?php echo date('Y-m-d H:i', strtotime($reply['created_at'])); ?>
                                                    </div>
                                                </div>
                                                <div class="reply-content mt-2">
                                                    <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
                                                </div>
                                                
                                                <?php if (!empty($reply['attachments'])): ?>
                                                    <div class="reply-attachments mt-2">
                                                        <h6>المرفقات:</h6>
                                                        <div class="attachments">
                                                            <?php
                                                            $attachments = json_decode($reply['attachments'], true);
                                                            if (is_array($attachments)) {
                                                                foreach ($attachments as $attachment) {
                                                                    echo '<div class="attachment">';
                                                                    echo '<a href="' . SITE_URL . '/uploads/support/' . $attachment . '" target="_blank">';
                                                                    echo '<i class="fa fa-file"></i> ' . $attachment;
                                                                    echo '</a>';
                                                                    echo '</div>';
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- نموذج إضافة رد -->
                    <!-- Add reply form -->
                    <?php if ($ticket['status'] !== 'closed'): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">إضافة رد</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (isset($errors) && !empty($errors)): ?>
                                            <div class="alert alert-danger">
                                                <ul class="mb-0">
                                                    <?php foreach ($errors as $error): ?>
                                                        <li><?php echo $error; ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form method="post" enctype="multipart/form-data">
                                            <div class="form-group mb-3">
                                                <label for="content">محتوى الرد</label>
                                                <textarea name="content" id="content" class="form-control" rows="5" required></textarea>
                                            </div>
                                            
                                            <?php if (isAdmin()): ?>
                                                <div class="form-check mb-3">
                                                    <input type="checkbox" name="is_private" id="is_private" class="form-check-input">
                                                    <label for="is_private" class="form-check-label">رد خاص (مرئي للمديرين فقط)</label>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="form-group mb-3">
                                                <label for="attachments">المرفقات</label>
                                                <input type="file" name="attachments[]" id="attachments" class="form-control" multiple>
                                                <small class="form-text text-muted">يمكنك تحميل ملفات متعددة (الحد الأقصى: 5 ملفات، 2 ميجابايت لكل ملف)</small>
                                            </div>
                                            
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fa fa-paper-plane"></i> إرسال الرد
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fa fa-lock"></i> هذه التذكرة مغلقة. لا يمكن إضافة ردود جديدة.
                            <a href="index.php?action=reopen&id=<?php echo $ticket['id']; ?>" class="alert-link">إعادة فتح التذكرة</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .ticket-content {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
    }
    
    .reply {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        border-left: 4px solid #6c757d;
    }
    
    .admin-reply {
        border-left-color: #007bff;
    }
    
    .user-reply {
        border-left-color: #28a745;
    }
    
    .attachments {
        margin-top: 10px;
    }
    
    .attachment {
        margin-bottom: 5px;
    }
</style>
