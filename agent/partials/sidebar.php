<?php
/**
 * Agent portal sidebar.
 * Variables:
 * - $agentNavActive — string: dashboard | properties | add-property | inquiries | profile
 *
 * Optional:
 * - $agentInquiryBadgeCount — int (avoids DB hit if caller already computed it)
 */
$agentNavActive = $agentNavActive ?? 'dashboard';

if (!isset($agentInquiryBadgeCount)) {
    $db = Database::getInstance();
    $agentInquiryBadgeCount = (int)$db->count(
        'inquiries',
        'agent_id = :aid AND status = :st',
        ['aid' => current_user_id(), 'st' => 'new']
    );
}
?>
<aside class="sidebar" id="agentSidebar">
    <div class="sidebar-header">
        <a href="<?php echo base_url(); ?>"><?php echo APP_NAME; ?></a>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="<?php echo $agentNavActive === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            Dashboard
        </a>
        <a href="properties.php" class="<?php echo $agentNavActive === 'properties' ? 'active' : ''; ?>">
            <i class="fas fa-building"></i>
            My Properties
        </a>
        <a href="add-property.php" class="<?php echo $agentNavActive === 'add-property' ? 'active' : ''; ?>">
            <i class="fas fa-plus-circle"></i>
            Add Property
        </a>
        <a href="inquiries.php" class="<?php echo $agentNavActive === 'inquiries' ? 'active' : ''; ?>">
            <i class="fas fa-envelope"></i>
            Inquiries
            <?php if ($agentInquiryBadgeCount > 0): ?>
                <span class="badge badge-error" style="margin-left:auto"><?php echo $agentInquiryBadgeCount; ?></span>
            <?php endif; ?>
        </a>
        <a href="profile.php" class="<?php echo $agentNavActive === 'profile' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            Profile
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="<?php echo base_url(); ?>">
            <i class="fas fa-arrow-left"></i>
            Back to Website
        </a>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>
    </div>
</aside>
