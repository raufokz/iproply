<?php
/**
 * Agent login / register: workspace picker (fieldset).
 *
 * Expected variables:
 * - $workspacesForPicker — array from WorkspaceContext::activeList()
 * - $selectedWorkspaceId — string (may be empty)
 */
if (empty($workspacesForPicker) || !is_array($workspacesForPicker)) {
    return;
}

$wsCount = count($workspacesForPicker);
$sel = isset($selectedWorkspaceId) ? (string)$selectedWorkspaceId : '';
$fid = 'ws-' . bin2hex(random_bytes(4));
?>
<fieldset class="ws-fieldset">
    <legend class="ws-legend">
        Workspace
        <?php if ($wsCount > 1): ?>
            <span class="ws-required-mark" aria-hidden="true">*</span>
        <?php endif; ?>
    </legend>
    <p class="ws-hint">
        Choose where you operate inside <?php echo sanitize(APP_NAME); ?>. Tools and defaults align to this workspace for your session.
    </p>

    <?php if ($wsCount === 1): ?>
        <?php $only = $workspacesForPicker[0]; ?>
        <div class="ws-single-card" role="status">
            <div class="ws-single-icon" aria-hidden="true">
                <i class="fas fa-<?php echo sanitize($only['icon']); ?>"></i>
            </div>
            <div class="ws-single-copy">
                <div class="ws-single-name"><?php echo sanitize($only['name']); ?></div>
                <?php if ($only['description'] !== ''): ?>
                    <div class="ws-single-desc"><?php echo sanitize($only['description']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <input type="hidden" name="workspace_id" value="<?php echo sanitize($only['id']); ?>">

    <?php else: ?>
        <div class="ws-stack" role="radiogroup" aria-labelledby="<?php echo $fid; ?>-label">
            <span id="<?php echo $fid; ?>-label" class="sr-only">Select a workspace</span>
            <?php
            $wsFirst = true;
            foreach ($workspacesForPicker as $w):
            ?>
                <label class="ws-card">
                    <input
                        class="ws-card-input"
                        type="radio"
                        name="workspace_id"
                        value="<?php echo sanitize($w['id']); ?>"
                        <?php echo $wsFirst ? 'required' : ''; ?>
                        <?php echo ($sel !== '' && $sel === $w['id']) ? 'checked' : ''; ?>
                    >
                    <span class="ws-card-body">
                        <span class="ws-card-radio" aria-hidden="true"></span>
                        <span class="ws-card-icon" aria-hidden="true"><i class="fas fa-<?php echo sanitize($w['icon']); ?>"></i></span>
                        <span class="ws-card-text">
                            <span class="ws-card-title"><?php echo sanitize($w['name']); ?></span>
                            <?php if ($w['description'] !== ''): ?>
                                <span class="ws-card-desc"><?php echo sanitize($w['description']); ?></span>
                            <?php endif; ?>
                        </span>
                    </span>
                </label>
            <?php
                $wsFirst = false;
            endforeach; ?>
        </div>
    <?php endif; ?>
</fieldset>
