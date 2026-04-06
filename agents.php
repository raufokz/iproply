<?php
/**
 * iProply - Agents Page (Compact Cards)
 */

require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Property.php';

$currentPage = 'agents';
$pageTitle = 'Our Agents';

$db = Database::getInstance();
$agents = $db->select('agents', '*', 'status = :status', ['status' => 'active'], 'is_featured DESC, created_at DESC');

include 'partials/header.php';
?>

<!-- Page Header -->
<header class="page-header">
    <div class="container">
        <h1>Meet Our Expert Agents</h1>
        <p>Passionate professionals dedicated to helping you find your dream home</p>
    </div>
</header>

<section class="section">
    <div class="container">
        <!-- Optional search bar -->
        <div class="agents-filter">
            <div class="filter-group">
                <input type="text" id="agentSearch" placeholder="Search by name..." class="filter-input">
            </div>
        </div>

        <?php if (empty($agents)): ?>
            <div class="empty-state">
                <i class="fas fa-users-slash"></i>
                <h3>No agents found</h3>
                <p>Check back later for our agent listings</p>
            </div>
        <?php else: ?>
            <div class="agents-grid compact" id="agentsGrid">
                <?php foreach ($agents as $agent): 
                    $fullName = sanitize($agent['first_name'] . ' ' . $agent['last_name']);
                    $avatar = $agent['avatar'] ? UPLOAD_URL . 'agents/' . $agent['avatar'] : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=1e3b5a&color=fff&size=400&bold=true';
                    $propertyCount = $db->count('properties', 'agent_id = :agent_id AND property_status = :status', [
                        'agent_id' => $agent['id'],
                        'status' => 'active'
                    ]);
                ?>
                    <div class="agent-card" data-agent-name="<?php echo strtolower($fullName); ?>">
                        <div class="agent-card-inner">
                            <div class="agent-card-image">
                                <img src="<?php echo $avatar; ?>" alt="<?php echo $fullName; ?>" loading="lazy">
                                <?php if ($agent['is_featured']): ?>
                                    <span class="featured-badge">Featured</span>
                                <?php endif; ?>
                            </div>
                            <div class="agent-card-content">
                                <h3><?php echo $fullName; ?></h3>
                                <p class="agent-title">Real Estate Agent</p>
                                
                                <!-- Stats row (horizontal) -->
                                <div class="agent-stats-row">
                                    <?php if ($agent['years_experience']): ?>
                                        <div class="agent-stat">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span><?php echo $agent['years_experience']; ?>+ yrs</span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="agent-stat">
                                        <i class="fas fa-building"></i>
                                        <span><?php echo $propertyCount; ?> listings</span>
                                    </div>
                                </div>
                                
                                <!-- Contact row -->
                                <div class="agent-contact-row">
                                    <?php if ($agent['phone']): ?>
                                        <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $agent['phone']); ?>" class="contact-link" title="Call">
                                            <i class="fas fa-phone-alt"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($agent['email']): ?>
                                        <a href="mailto:<?php echo sanitize($agent['email']); ?>" class="contact-link" title="Email">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <a href="listings.php?agent=<?php echo $agent['id']; ?>" class="btn btn-sm agent-listings-btn">
                                    View Listings <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('agentSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const agents = document.querySelectorAll('.agent-card');
            agents.forEach(agent => {
                const name = agent.getAttribute('data-agent-name');
                agent.style.display = name && name.includes(searchTerm) ? '' : 'none';
            });
        });
    }
});
</script>

<?php include 'partials/footer.php'; ?>