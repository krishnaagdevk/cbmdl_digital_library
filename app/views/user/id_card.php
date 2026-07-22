<?php
// views/user/id_card.php
if (!defined('BASE_URL')) exit;
?>
<div class="grid" style="grid-template-columns: 1fr 1fr; gap: 30px;">
    <div class="card" style="display:flex; flex-direction:column; align-items:center;">
        <h3><i class="fa-solid fa-address-card"></i> Double-Sided Membership Card</h3>
        <p style="font-size: 13px; color: var(--text-muted); text-align: center; margin-bottom: 20px;">Premium double-sided printable layout. Sized exactly to standard CR80 wallet card specifications.</p>
        
        <!-- Double Sided Print Wrapper -->
        <div id="membership-card-print" style="display:flex; flex-direction:column; gap:15px; align-items:center;">
            
            <!-- STYLING FOR PRINT ALIGNMENT -->
            <style>
                @media print {
                    body * { visibility: hidden; }
                    #membership-card-print, #membership-card-print * { visibility: visible; }
                    #membership-card-print {
                        position: absolute;
                        left: 0;
                        top: 0;
                        width: 100%;
                        display: flex !important;
                        flex-direction: row !important;
                        justify-content: center !important;
                        gap: 20px !important;
                        padding: 0 !important;
                        margin: 0 !important;
                    }
                    .cr80-card {
                        box-shadow: none !important;
                        border: 1px solid #000 !important;
                        page-break-inside: avoid;
                    }
                }
            </style>

            <!-- CARD FRONT -->
            <div class="cr80-card" style="width: 340px; height: 215px; border: 2px solid var(--primary); border-radius: 12px; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow:hidden; font-family:\'Inter\', sans-serif; display:flex; flex-direction:column; justify-content:space-between; position:relative;">
                <!-- Header Banner -->
                <div style="background: linear-gradient(135deg, var(--navy-dark), var(--navy-light)); color: white; padding: 10px 15px; text-align: center; border-bottom: 2px solid var(--primary); display:flex; align-items:center; gap:10px; justify-content:center;">
                    <div style="font-size:18px;">🏛️</div>
                    <div>
                        <h4 style="margin: 0; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight:700;">Meerut Cantonment Board</h4>
                        <p style="margin: 2px 0 0 0; font-size: 9px; opacity: 0.9; font-weight: 500;">P.L.C.L. Digital e-Library</p>
                    </div>
                </div>
                
                <!-- Body Section -->
                <div style="padding: 12px 15px; display: flex; gap: 15px; align-items: center; flex-grow:1;">
                    <!-- User Avatar -->
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: #eff6ff; display:flex; align-items:center; justify-content:center; border: 2px solid var(--border-color); font-size: 28px; flex-shrink:0;">
                        👤
                    </div>
                    <!-- Details -->
                    <div style="overflow:hidden;">
                        <h4 style="margin:0; font-size: 14px; color: var(--navy-dark); white-space:nowrap; text-overflow:ellipsis; overflow:hidden;"><?= e($me['name']) ?></h4>
                        <p style="margin:3px 0 0 0; font-size: 11px; font-weight: 700; color: var(--primary);"><?= e($me['membership_id']) ?></p>
                        <p style="margin:2px 0 0 0; font-size: 10px; color: var(--text-muted); white-space:nowrap; text-overflow:ellipsis; overflow:hidden;">Mob: <?= e($me['mobile']) ?></p>
                        <p style="margin:2px 0 0 0; font-size: 10px; color: var(--text-muted); white-space:nowrap; text-overflow:ellipsis; overflow:hidden;">UID: <?= e(substr($me['aadhar_no'], 0, 4) . ' ' . substr($me['aadhar_no'], 4, 4) . ' ' . substr($me['aadhar_no'], 8)) ?></p>
                    </div>
                </div>
                
                <!-- Footer Section -->
                <div style="background: #f8fafc; padding: 6px 15px; border-top: 1px dashed var(--border-color); font-size: 9px; color: var(--text-color); display: flex; justify-content: space-between;">
                    <div>
                        <span style="color: var(--text-muted); font-size: 8px; text-transform: uppercase; display:block;">Shift</span>
                        <strong style="color:var(--primary);"><?= e($me['shift'] ?? 'Both') ?></strong>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); font-size: 8px; text-transform: uppercase; display:block;">Issued</span>
                        <strong><?= date('d-m-Y', strtotime($me['start_date'])) ?></strong>
                    </div>
                    <div style="text-align: right;">
                        <span style="color: var(--text-muted); font-size: 8px; text-transform: uppercase; display:block;">Expires</span>
                        <strong style="color: var(--accent-red);"><?= date('d-m-Y', strtotime($me['end_date'])) ?></strong>
                    </div>
                </div>
                
            </div>

            <!-- CARD BACK -->
            <div class="cr80-card" style="width: 340px; height: 215px; border: 2px solid var(--primary); border-radius: 12px; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow:hidden; font-family:\'Inter\', sans-serif; display:flex; flex-direction:column; justify-content:space-between; text-align:center; padding: 12px 15px;">
                <!-- Instructions / Rules -->
                <div>
                    <h5 style="margin: 0 0 8px 0; font-size: 11px; text-transform: uppercase; color: var(--navy-dark); font-weight: 700; border-bottom:1px solid var(--border-color); padding-bottom:4px;">Library Instructions & Terms</h5>
                    <ul style="margin: 0; padding-left: 14px; text-align: left; font-size: 8px; color: var(--text-dark); line-height: 1.4; display:flex; flex-direction:column; gap:3px;">
                        <li>This card is non-transferable and remains property of MCB.</li>
                        <li>Show this card at the check-out desk for physical book lending.</li>
                        <li>Overdue physical volumes must be returned promptly to the library desk.</li>
                        <li>Loss of membership card must be reported to librarian instantly.</li>
                        <li>Digital portal access is active till subscription expiry.</li>
                    </ul>
                </div>

                <!-- Barcode & Stamp Simulation -->
                <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-top:8px;">
                    <!-- Barcode block -->
                    <div style="text-align:left;">
                        <div style="display:flex; align-items:flex-end; height:24px; background:white; padding:2px; border-radius:2px; border:1px solid #ddd;">
                            <div style="width:2px; height:100%; background:black; margin-right:1px;"></div>
                            <div style="width:1px; height:100%; background:black; margin-right:1px;"></div>
                            <div style="width:3px; height:100%; background:black; margin-right:1px;"></div>
                            <div style="width:1px; height:100%; background:black; margin-right:2px;"></div>
                            <div style="width:2px; height:100%; background:black; margin-right:1px;"></div>
                            <div style="width:4px; height:100%; background:black; margin-right:1px;"></div>
                            <div style="width:1px; height:100%; background:black; margin-right:1px;"></div>
                            <div style="width:2px; height:100%; background:black; margin-right:2px;"></div>
                            <div style="width:3px; height:100%; background:black; margin-right:1px;"></div>
                            <div style="width:1px; height:100%; background:black; margin-right:1px;"></div>
                            <div style="width:2px; height:100%; background:black; margin-right:1px;"></div>
                            <div style="width:1px; height:100%; background:black; margin-right:2px;"></div>
                            <div style="width:4px; height:100%; background:black; margin-right:1px;"></div>
                            <div style="width:2px; height:100%; background:black; margin-right:1px;"></div>
                            <div style="width:1px; height:100%; background:black; margin-right:1px;"></div>
                        </div>
                        <span style="font-family:\'Courier New\', monospace; font-size: 8px; font-weight:700; color:var(--text-dark); display:block; margin-top:2px;"><?= e($me['membership_id']) ?></span>
                    </div>

                    <!-- Stamp / Seal Info -->
                    <div style="text-align:right; border-top:1px solid #777; width:90px; padding-top:2px;">
                        <span style="font-size:7px; color:var(--navy-light); font-weight:700; text-transform:uppercase; display:block;">Issued By Authority</span>
                        <span style="font-size:7px; color:var(--text-muted); display:block;">CBMDL</span>
                    </div>
                </div>
            </div>

        </div>
        
        <button class="btn" style="margin-top:20px; background: var(--primary);" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Double-Sided Card</button>
    </div>
    
    <div class="card">
        <h3><i class="fa-solid fa-receipt"></i> Membership Payment Receipt</h3>
        <div id="membership-receipt-print" style="max-width: 500px; margin: 0 auto; background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 30px; font-family:\'Inter\', sans-serif;">
            <div style="text-align: center; border-bottom: 2px double var(--border-color); padding-bottom: 15px; margin-bottom: 20px;">
                <h3 style="margin: 0; font-size: 18px; color: var(--navy-dark);">OFFICIAL PAYMENT RECEIPT</h3>
                <p style="margin: 4px 0 0 0; font-size: 25px; color: var(--text-muted);">P.L.C.L. Cantonment Board Library, Meerut</p>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 15px;">
                <div>
                    <span style="color: var(--text-muted);">Receipt To:</span>
                    <strong style="display:block; font-size: 14px; margin-top:2px;"><?= e($me['name']) ?></strong>
                    <span style="color: var(--text-muted); font-size: 12px;"><?= e($me['membership_id']) ?></span>
                </div>
                <div style="text-align: right;">
                    <span style="color: var(--text-muted);">Receipt Date:</span>
                    <strong style="display:block; margin-top:2px;"><?= date('d-m-Y', strtotime($me['created_at'])) ?></strong>
                </div>
            </div>
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin: 20px 0;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 8px 0; text-align: left; background: none;">Description</th>
                        <th style="padding: 8px 0; text-align: right; background: none;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 0;">Library digital portal access enrollment (<?= e($me['duration']) ?> subscription)</td>
                        <td style="padding: 10px 0; text-align: right; font-weight: 600;">₹<?= number_style_format($me['membership_fee'] ?? 200.00) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: 700; text-align: right;">Total Amount Paid:</td>
                        <td style="padding: 10px 0; text-align: right; font-weight: 700; color: var(--primary); font-size: 15px;">₹<?= number_style_format($me['membership_fee'] ?? 200.00) ?></td>
                    </tr>
                </tbody>
            </table>
            <div style="background: #f8fafc; border-radius: 8px; padding: 12px; font-size: 12px; margin-top: 15px;">
                <p style="margin: 0;"><strong>Payment Transaction ID:</strong> <?= e($me['payment_id']) ?></p>
                <p style="margin: 4px 0 0 0; color: var(--text-muted);">Status: Success (Offline Settled)</p>
            </div>
            <div style="margin-top: 30px; text-align: center; font-size: 11px; color: var(--text-muted); border-top: 1px solid var(--border-color); padding-top: 15px;">
                Thank you for your support of our cantonment library community.
            </div>
        </div>
        <div style="text-align:center; margin-top:20px;">
            <button class="btn" style="background: var(--navy-light);" onclick="printElement(\'membership-receipt-print\')"><i class="fa-solid fa-print"></i> Print Payment Receipt</button>
        </div>
    </div>
</div>
