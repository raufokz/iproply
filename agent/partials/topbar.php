<?php
/**
 * Agent portal top bar.
 * Variables:
 * - $agentPageHeading — string page title shown in H1
 *
 * Optional:
 * - $agentTopbarVariant — 'session' | 'profile' (default session)
 * - $agent — agent row array when variant profile
 * - $avatarUrl — string URL when variant profile & avatar exists
 *
 * Workspace label uses WorkspaceContext unless $agentWorkspaceLabel is preset (usually leave unset).
 */
$agentTopbarVariant = $agentTopbarVariant ?? 'session';
$heading = isset($agentPageHeading) ? (string)$agentPageHeading : 'Agent';

if (!isset($agentWorkspaceLabel)) {
    $agentWorkspaceLabel = WorkspaceContext::displayName();
}
?>

<header class="topbar">
    <div class="topbar-title">
        <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="agentSidebar" aria-expanded="false" aria-label="Open menu">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>
        <h1><?php echo sanitize($heading); ?></h1>
        <?php if (!empty($agentWorkspaceLabel)): ?>
            <span class="topbar-workspace" title="Active workspace for this session">
                <i class="fas fa-layer-group" aria-hidden="true"></i>
                <span><?php echo sanitize($agentWorkspaceLabel); ?></span>
            </span>
        <?php endif; ?>
    </div>

    <div class="topbar-user">
        <?php if ($agentTopbarVariant === 'profile' && !empty($agent) && is_array($agent)): ?>
            <div class="user-info">
                <div class="name"><?php echo sanitize(trim($agent['first_name'] . ' ' . $agent['last_name'])); ?></div>
                <div class="role">Real Estate Agent</div>
            </div>
            <?php if (!empty($agent['avatar']) && !empty($avatarUrl)): ?>
                <img src="<?php echo sanitize($avatarUrl); ?>" alt="" class="user-avatar user-avatar--img">
            <?php else: ?>
                <div class="user-avatar">
                    <?php
                    $nm = trim(($agent['first_name'] ?? '') . ' ' . ($agent['last_name'] ?? ''));
                    echo strtoupper(substr($nm !== '' ? $nm : ($_SESSION['user_name'] ?? 'A'), 0, 1));
                    ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="user-info">
                <div class="name"><?php echo sanitize($_SESSION['user_name'] ?? 'Agent'); ?></div>
                <div class="role">Real Estate Agent</div>
            </div>
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?>
            </div>
        <?php endif; ?>
    </div>
</header>
