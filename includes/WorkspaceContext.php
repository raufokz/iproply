<?php
/**
 * Workspace selection for the agent portal (session-scoped).
 */
class WorkspaceContext
{
    public const SESSION_ID = 'agent_workspace_id';
    public const SESSION_NAME = 'agent_workspace_name';
    public const SESSION_PREFILL = 'agent_workspace_prefill_id';

    /**
     * @return array<int, array{id:string,name:string,description:string,icon:string,active:bool}>
     */
    public static function activeList(): array
    {
        $path = BASE_PATH . '/config/workspaces.php';
        if (!is_readable($path)) {
            return [];
        }
        $raw = require $path;
        if (!is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $row) {
            if (empty($row['active'])) {
                continue;
            }
            $id = trim((string)($row['id'] ?? ''));
            $name = trim((string)($row['name'] ?? ''));
            if ($id === '' || $name === '') {
                continue;
            }
            $out[] = [
                'id' => $id,
                'name' => $name,
                'description' => trim((string)($row['description'] ?? '')),
                'icon' => preg_replace('/^fa\-/', '', trim((string)($row['icon'] ?? 'building'))),
                'active' => true,
            ];
        }
        return $out;
    }

    public static function findById(?string $id): ?array
    {
        if ($id === null || $id === '') {
            return null;
        }
        foreach (self::activeList() as $w) {
            if ($w['id'] === $id) {
                return $w;
            }
        }
        return null;
    }

    /**
     * Validate workspace input for login / registration forms.
     *
     * @return array{ok:bool,errors:string[],resolved_id:?string,resolved_name:?string}
     */
    public static function validateForForm(?string $postedId): array
    {
        $list = self::activeList();
        $n = count($list);

        if ($n === 0) {
            return ['ok' => true, 'errors' => [], 'resolved_id' => null, 'resolved_name' => null];
        }

        if ($n === 1) {
            return [
                'ok' => true,
                'errors' => [],
                'resolved_id' => $list[0]['id'],
                'resolved_name' => $list[0]['name'],
            ];
        }

        $postedId = trim((string)($postedId ?? ''));
        if ($postedId === '') {
            return [
                'ok' => false,
                'errors' => ['Please choose a workspace before continuing.'],
                'resolved_id' => null,
                'resolved_name' => null,
            ];
        }

        $found = self::findById($postedId);
        if (!$found) {
            return [
                'ok' => false,
                'errors' => ['That workspace is not available. Please pick one from the list.'],
                'resolved_id' => null,
                'resolved_name' => null,
            ];
        }

        return [
            'ok' => true,
            'errors' => [],
            'resolved_id' => $found['id'],
            'resolved_name' => $found['name'],
        ];
    }

    public static function persistToSession(?string $id, ?string $name): void
    {
        if ($id === null || $id === '') {
            unset($_SESSION[self::SESSION_ID], $_SESSION[self::SESSION_NAME]);
            return;
        }
        $_SESSION[self::SESSION_ID] = $id;
        $_SESSION[self::SESSION_NAME] = (string)$name;
    }

    public static function displayName(): ?string
    {
        $name = $_SESSION[self::SESSION_NAME] ?? null;
        return ($name !== null && $name !== '') ? $name : null;
    }

    public static function prefillId(): ?string
    {
        $p = $_SESSION[self::SESSION_PREFILL] ?? null;
        return ($p !== null && $p !== '') ? (string)$p : null;
    }

    public static function setPrefill(?string $id): void
    {
        if ($id === null || $id === '') {
            unset($_SESSION[self::SESSION_PREFILL]);
            return;
        }
        $_SESSION[self::SESSION_PREFILL] = $id;
    }

    public static function clearPrefill(): void
    {
        unset($_SESSION[self::SESSION_PREFILL]);
    }
}
