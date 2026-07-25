<?php
// views/admin/ebooks.php
if (!defined('BASE_URL')) exit;

$editId = (int)($_GET['edit'] ?? 0);
$editBook = null;
if ($editId) {
    $ebStmt = $db->prepare("SELECT * FROM ebooks WHERE id = ?");
    $ebStmt->bind_param("i", $editId);
    $ebStmt->execute();
    $editBook = $ebStmt->get_result()->fetch_assoc();
    $ebStmt->close();
}

// Fetch latest 5 uploaded ebooks for quick management
$latest_ebooks_result = $db->query("SELECT e.*, c.name category FROM ebooks e JOIN categories c ON c.id = e.category_id ORDER BY e.id DESC LIMIT 5");
?>

<div class="grid">
    <?php if ($editBook): ?>
        <div class="card">
            <h3><i class="fa-solid fa-user-pen"></i> Edit E-Book Document — <?= e($editBook['title']) ?></h3>
            <form id="ebFormEdit" onsubmit="handleChunkedSubmit(event, true)">
                <?= csrf_input() ?>
                <input type="hidden" name="id" value="<?= $editBook['id'] ?>">
                
                <label for="eb_category">Category</label>
                <select id="eb_category" name="category_id" required>
                    <option value="">Choose Catalog Category</option>
                    <?php 
                    $x = $db->query('SELECT * FROM categories ORDER BY name');
                    while($r = $x->fetch_assoc()) {
                        $selected = $r['id'] == $editBook['category_id'] ? 'selected' : '';
                        echo '<option value="' . $r['id'] . '" ' . $selected . '>' . e($r['name']) . '</option>';
                    }
                    ?>
                </select>
                
                <label for="eb_title">E-Book Title</label>
                <input id="eb_title" name="title" value="<?= e($editBook['title']) ?>" placeholder="Full volume/book title" required>
                
                <label for="eb_keywords">Author (Publisher)</label>
                <input id="eb_keywords" name="keywords" value="<?= e($editBook['keywords']) ?>" placeholder="e.g. upsc, mathematics, calculus">
                
                <label for="eb_pdf">Replace E-Book PDF Document (leave blank to keep current PDF)</label>
                <input id="eb_pdf" type="file" name="pdf" accept="application/pdf">
                
                <button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Save / Update E-Book</button>
                <a href="?action=admin&tab=ebooks" class="btn" style="background:var(--navy-light); margin-left: 10px;"><i class="fa-solid fa-arrow-left"></i> Cancel Edit</a>
            </form>
        </div>
    <?php else: ?>
        <div class="card">
            <h3><i class="fa-solid fa-cloud-arrow-up"></i> Upload E-Book Document</h3>
            <form id="ebFormAdd" onsubmit="handleChunkedSubmit(event, false)">
                <?= csrf_input() ?>
                
                <label for="eb_category">Select Category</label>
                <select id="eb_category" name="category_id" required>
                    <option value="">Choose Catalog Category</option>
                    <?php 
                    $x = $db->query('SELECT * FROM categories ORDER BY name');
                    while($r = $x->fetch_assoc()) {
                        echo '<option value="' . $r['id'] . '">' . e($r['name']) . '</option>';
                    }
                    ?>
                </select>
                
                <label for="eb_title">E-Book Title</label>
                <input id="eb_title" name="title" placeholder="Enter e-book title" required>
                
                <label for="eb_keywords">Author (Publisher)</label>
                <input id="eb_keywords" name="keywords" placeholder="e.g. H.C. Verma (Arihant Publications)">
                
                <label for="eb_pdf">Choose E-Book PDF Document</label>
                <input id="eb_pdf" type="file" name="pdf" accept="application/pdf" required>
                
                <button class="btn" type="submit"><i class="fa-solid fa-circle-arrow-up"></i> Begin Chunked Upload</button>
            </form>
        </div>

        <!-- Bulk CSV Tools for Admin -->
        <div class="card" style="border: 1px dashed var(--border-color); background: var(--bg-slate);">
            <h3 style="margin-top:0; color:var(--navy-dark);"><i class="fa-solid fa-file-csv" style="color: #10b981;"></i> Bulk CSV Catalog & Local Backups</h3>
            <p style="font-size:12px; color:var(--text-muted); margin-bottom:15px;">Export local library catalog backups to CSV spreadsheet or batch-import new ebook records into catalog database.</p>
            <div style="display:flex; gap:10px; margin-bottom:15px; flex-wrap:wrap;">
                <a href="?action=export_ebooks_csv" class="btn" style="background:var(--navy-dark); font-size:12px; display:inline-flex; align-items:center; gap:5px;"><i class="fa-solid fa-download"></i> Export E-Books CSV</a>
            </div>
       
        </div>
    <?php endif; ?>
</div>

<!-- Concurrent Uploads Progress Status Board -->
<div id="uploadStatusBoard" class="card" style="display:none; border-left: 4px solid var(--accent-orange); margin-top:20px;">
    <h3 style="margin-top:0; margin-bottom:15px; color:var(--navy-dark);"><i class="fa-solid fa-spinner fa-spin" style="color:var(--accent-orange);"></i> Background Parallel Upload Operations</h3>
    <div id="uploadCardsContainer" style="display:grid; grid-template-columns:1fr; gap:12px;"></div>
</div>

<!-- Recently Uploaded E-Books (Latest 5) -->
<div class="card" style="margin-top: 25px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:15px;">
        <h3 style="margin:0;"><i class="fa-solid fa-clock-rotate-left"></i> Recently Added E-Books (Latest 5)</h3>
        <a href="?action=admin&tab=view_ebooks" class="btn" style="padding:8px 16px; background:var(--primary); font-size:13px; display:inline-flex; align-items:center; gap:6px;"><i class="fa-solid fa-book-open"></i> View All E-Books &rarr;</a>
    </div>
    
    <div class="table-responsive">
        <table id="ebooksTable">
            <thead>
                <tr>
                    <th>E-Book Title</th>
                    <th>Author (Publisher)</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($latest_ebooks_result->num_rows === 0): ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding:30px; color:var(--text-muted);">
                            <i class="fa-solid fa-triangle-exclamation" style="font-size:24px; margin-bottom:10px; display:block;"></i>
                            No e-books uploaded yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while($r = $latest_ebooks_result->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight:600; color:var(--navy-dark);"><?= e($r['title']) ?></td>
                            <td style="font-size:12px; color:var(--text-secondary);"><?= e($r['keywords'] ?: '—') ?></td>
                            <td><span class="badge badge-blue" style="font-size:11px; padding:3px 8px; font-weight:600;"><?= e($r['category']) ?></span></td>
                            <td>
                                <div style="display:flex; gap:5px;">
                                    <a class="btn" style="background:var(--navy-light); padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:4px;" href="?action=view_pdf_content&id=<?= $r['id'] ?>" target="_blank"><i class="fa-solid fa-eye"></i> View PDF</a> 
                                    <a class="btn" style="background:var(--primary); padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:4px;" href="?action=admin&tab=ebooks&edit=<?= $r['id'] ?>"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                    <form method="post" action="?action=delete_ebook" class="delete-form" style="display:inline; margin:0;">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button class="danger btn btn-danger" type="submit" style="padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-trash-can"></i> Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chunked Parallel Upload Controller Script -->
<script class="dynamic-script">
if (typeof escapeHtml !== 'function') {
    window.escapeHtml = function(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }
}

function handleChunkedSubmit(e, isEdit) {
    e.preventDefault();
    const form = e.target;
    const catSelect = form.querySelector('[name="category_id"]');
    const titleInput = form.querySelector('[name="title"]');
    const kwInput = form.querySelector('[name="keywords"]');
    const fileInput = form.querySelector('[name="pdf"]');
    
    if (!catSelect.value || !titleInput.value) {
        if (window.showToast) window.showToast('⚠️ Category and Title are mandatory parameters.', 'error');
        return;
    }
    
    const file = fileInput.files ? fileInput.files[0] : null;
    
    // If editing and no file selected, submit standard HTML fallback to update metadata only
    if (isEdit && !file) {
        const btn = form.querySelector('button[type="submit"]');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving changes...';
        
        const fd = new FormData(form);
        fetch('?action=update_ebook', {
            method: 'POST',
            body: fd
        })
        .then(response => {
            if (!response.ok) throw new Error('Metadata update failed');
            if (window.navigateToUrl) {
                navigateToUrl('?action=admin&tab=ebooks');
            } else {
                window.location.reload();
            }
        })
        .catch(err => {
            if (window.showToast) window.showToast('⚠️ ' + (err.message || 'Metadata update failed.'), 'error');
            btn.disabled = false;
            btn.innerHTML = origText;
        });
        return;
    }
    
    if (!file) {
        if (window.showToast) window.showToast('⚠️ Please choose a valid PDF book to upload.', 'error');
        return;
    }
    
    if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
        if (window.showToast) window.showToast('⚠️ Security check failed: Only application/pdf files are permitted inside e-Library catalogs.', 'error');
        return;
    }

    const metadata = {
        category_id: catSelect.value,
        title: titleInput.value,
        keywords: kwInput.value,
        ebook_id: isEdit ? form.querySelector('[name="id"]').value : null
    };

    // Instantiate and launch the UploadManager
    const manager = new UploadManager(
        file,
        metadata,
        // onProgress
        (uploaded, total) => {
            const card = document.getElementById(`up-card-${manager.uploadId}`);
            if (!card) return;
            const pct = Math.round((uploaded / total) * 100);
            card.querySelector('.upload-pct-badge').textContent = `${pct}%`;
            card.querySelector('.upload-progress-fill').style.width = `${pct}%`;
            card.querySelector('.upload-status-badge').textContent = `Uploading ${uploaded}/${total}`;
            const mbTransferred = (uploaded * manager.chunkSize / (1024 * 1024)).toFixed(1);
            card.querySelector('.upload-progress-text').innerHTML = `<i class="fa-solid fa-arrow-up-from-bracket"></i> Transferred: <strong>${mbTransferred}MB</strong> / ${(file.size / (1024 * 1024)).toFixed(1)}MB`;
        },
        // onComplete
        (response) => {
            const card = document.getElementById(`up-card-${manager.uploadId}`);
            if (!card) return;
            card.querySelector('.upload-pct-badge').textContent = '100%';
            card.querySelector('.upload-progress-fill').style.width = '100%';
            card.querySelector('.upload-progress-fill').style.background = 'var(--accent-green)';
            
            const badge = card.querySelector('.upload-status-badge');
            badge.textContent = 'Completed ✅';
            badge.className = 'badge badge-green';
            
            card.querySelector('.upload-progress-text').innerHTML = '✅ Merged and cataloged successfully!';
            card.querySelector('.upload-cancel-btn').style.display = 'none';
            if (window.showToast) window.showToast('E-book uploaded successfully.', 'success');
            
            // Only reload/navigate if there are NO OTHER active uploads in progress!
            const runningCount = Object.keys(window.activeUploads || {}).length;
            if (runningCount === 0) {
                setTimeout(() => {
                    if (window.navigateToUrl) {
                        navigateToUrl('?action=admin&tab=ebooks');
                    } else {
                        window.location.reload();
                    }
                }, 1500);
            } else {
                card.querySelector('.upload-progress-text').innerHTML = '✅ Merged and cataloged successfully! (The list will refresh after other active uploads finish)';
            }
        },
        // onError
        (err) => {
            const card = document.getElementById(`up-card-${manager.uploadId}`);
            if (!card) return;
            card.querySelector('.upload-pct-badge').textContent = 'Failed ❌';
            card.querySelector('.upload-progress-fill').style.width = '100%';
            card.querySelector('.upload-progress-fill').style.background = 'var(--accent-red)';
            
            const badge = card.querySelector('.upload-status-badge');
            badge.textContent = 'Failed ❌';
            badge.className = 'badge badge-red';
            
            card.querySelector('.upload-progress-text').innerHTML = `<span style="color:var(--accent-red); font-weight:600;"><i class="fa-solid fa-triangle-exclamation"></i> Error: ${escapeHtml(err)}</span>`;
            card.querySelector('.upload-cancel-btn').style.display = 'none';
            if (window.showToast) window.showToast('⚠️ Upload failed: ' + err, 'error');
        }
    );

    // Append operations progress card
    const cardHtml = `
        <div class="card" id="up-card-${manager.uploadId}" style="background:var(--bg-slate); border:1px solid var(--border-color); border-radius:12px; padding:15px; display:flex; flex-direction:column; gap:8px; margin:0;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <strong style="font-size:14px; color:var(--navy-dark); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:70%;"><i class="fa-solid fa-file-pdf"></i> ${escapeHtml(file.name)}</strong>
                <span class="badge badge-blue upload-status-badge" style="font-size:11px; padding:4px 8px;">Queued</span>
            </div>
            <div style="font-size:12px; color:var(--text-muted); display:flex; justify-content:space-between;">
                <span>Total Size: ${(file.size / (1024 * 1024)).toFixed(2)} MB</span>
                <span class="upload-pct-badge" style="font-weight:700;">0%</span>
            </div>
            <div style="background:var(--border-color); height:8px; border-radius:4px; overflow:hidden;">
                <div class="upload-progress-fill" style="background:var(--primary); height:100%; width:0%; transition:width 0.2s ease;"></div>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:5px;">
                <span class="upload-progress-text" style="font-size:11px; color:var(--text-muted);">Preparing local chunks slices...</span>
                <button type="button" class="danger btn btn-danger upload-cancel-btn" style="padding:4px 8px; font-size:11px; background:var(--accent-red); margin:0;" onclick="cancelChunkedUpload('${manager.uploadId}')"><i class="fa-solid fa-circle-xmark"></i> Cancel</button>
            </div>
        </div>
    `;
    
    document.getElementById('uploadCardsContainer').insertAdjacentHTML('beforeend', cardHtml);
    document.getElementById('uploadStatusBoard').style.display = 'block';

    // Reset inputs for parallel additions
    titleInput.value = '';
    kwInput.value = '';
    if (fileInput) fileInput.value = '';

    manager.start();
}

if (typeof cancelChunkedUpload !== 'function') {
    window.cancelChunkedUpload = function(uuid) {
        if (window.activeUploads && window.activeUploads[uuid]) {
            if (confirm('Cancel this background upload and discard all temporary chunks?')) {
                window.activeUploads[uuid].cancel();
                const card = document.getElementById(`up-card-${uuid}`);
                if (card) {
                    card.querySelector('.upload-status-badge').textContent = 'Cancelled';
                    card.querySelector('.upload-status-badge').className = 'badge badge-red';
                    card.querySelector('.upload-progress-fill').style.background = 'var(--text-muted)';
                    card.querySelector('.upload-progress-text').innerHTML = '<span style="color:var(--text-muted);"><i class="fa-solid fa-ban"></i> Upload cancelled by librarian.</span>';
                    card.querySelector('.upload-cancel-btn').style.display = 'none';
                    setTimeout(() => {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(10px)';
                        setTimeout(() => {
                            card.remove();
                            const container = document.getElementById('uploadCardsContainer');
                            if (container.children.length === 0) {
                                document.getElementById('uploadStatusBoard').style.display = 'none';
                            }
                        }, 400);
                    }, 1500);
                }
            }
        }
    }
}
</script>
