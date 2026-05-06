<?php
/**
 * Agent portal workspaces (region, desk, or brand context).
 * Return an empty array to hide workspace selection entirely (no DB change required).
 *
 * Each item:
 * - id: unique string (stable for sessions)
 * - name: short label
 * - description: one line shown in cards
 * - icon: Font Awesome 6 class without "fa-" prefix (e.g. "globe")
 * - active: bool
 */
return [
    [
        'id' => 'marketplace-national',
        'name' => 'National marketplace',
        'description' => 'Full iProply listing exposure, inquiries, and core agent tools for broad coverage.',
        'icon' => 'globe',
        'active' => true,
    ],
    [
        'id' => 'desk-referral',
        'name' => 'Referral & introductions',
        'description' => 'Structured handoffs, partner workflows, and referral-friendly reporting.',
        'icon' => 'handshake',
        'active' => true,
    ],
    [
        'id' => 'desk-luxury',
        'name' => 'Luxury & advisory',
        'description' => 'High-touch positioning, concierge-style follow-up, and premium presentation.',
        'icon' => 'gem',
        'active' => true,
    ],
];
