<?php
/**
 * iProply - Blog Listing Page
 */
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Blog.php';

$currentPage = 'blog';
$pageTitle = 'Insights & Real Estate Guides';

$blogModel = new Blog();
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 5;
$searchQ = trim((string) ($_GET['q'] ?? ''));

$totalPosts = $blogModel->countPublished($searchQ);
$totalPages = $totalPosts > 0 ? max(1, (int) ceil($totalPosts / $perPage)) : 1;
if ($page > $totalPages) {
    $page = $totalPages;
}

$posts = $blogModel->getPublished($page, $perPage, $searchQ);

$metaTitle = 'Blog & Insights | iProply';
$metaDescription = 'Guides, market perspective, and tips for buyers, renters, sellers, and agents — curated by the iProply team.';
if ($searchQ !== '') {
    $metaTitle = 'Search: ' . $searchQ . ' | iProply Blog';
    $metaDescription = 'Blog posts matching "' . $searchQ . '" on iProply.';
}

// Sidebar: recent posts (always unfiltered so the rail stays useful while searching)
$recentPosts = $blogModel->getPublished(1, 4);

include 'partials/header.php';

$blogPaginationQuery = [];
if ($searchQ !== '') {
    $blogPaginationQuery['q'] = $searchQ;
}
?>

<!-- Hero Section -->
<section class="page-hero" style="background: linear-gradient(135deg, var(--navy-900) 0%, var(--navy-700) 100%); padding: 160px 0 100px; color: white; text-align: center;">
    <div class="container">
        <span style="color: var(--gold-200); font-family: var(--font-family); text-transform: uppercase; letter-spacing: 0.15em; font-size: 0.85rem; font-weight: 600; display: block; margin-bottom: 1rem;">Expert Market Analysis</span>
        <h1 style="font-size: 3.5rem; font-weight: 700; margin-bottom: 1.5rem; font-family: var(--font-family);">iProply Insights</h1>
        <?php if ($searchQ !== ''): ?>
            <p style="font-size: 1.1rem; color: rgba(255,255,255,0.9); max-width: 640px; margin: 0 auto;">
                Results for <strong style="color: #fff;"><?php echo sanitize($searchQ); ?></strong>
                · <a href="<?php echo base_url('blog.php'); ?>" style="color: var(--gold-200); text-decoration: underline;">Clear search</a>
            </p>
        <?php else: ?>
            <p style="font-size: 1.1rem; color: rgba(255,255,255,0.8); max-width: 600px; margin: 0 auto;">Master the US real estate market with our curated guides, data-driven tips, and expert strategies.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Blog Layout Section -->
<section class="section" style="padding: 5rem 0; background-color: var(--cream);">
    <div class="container">
        
        <div style="display: grid; grid-template-columns: 1fr; gap: 3rem;">
            <!-- Adjust grid for desktop: 70% left, 30% right -->
            <style>
                @media (min-width: 1024px) {
                    .blog-layout { display: grid; grid-template-columns: 7fr 3fr; gap: 3rem; align-items: start; }
                }
            </style>
            
            <div class="blog-layout">
                
                <!-- Left Side: Main Content (70%) -->
                <div>
                    <?php if (empty($posts)): ?>
                        <div style="padding: 4rem; text-align: center; background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                            <i class="fas fa-pen-nib" style="font-size: 3rem; color: var(--gold-200); margin-bottom: 1rem;"></i>
                            <?php if ($searchQ !== ''): ?>
                                <h3 style="font-size: 1.5rem; color: var(--navy-800); margin-bottom: 0.5rem;">No matching articles</h3>
                                <p style="color: var(--text-secondary);">Try another keyword or <a href="<?php echo base_url('blog.php'); ?>">browse all posts</a>.</p>
                            <?php else: ?>
                                <h3 style="font-size: 1.5rem; color: var(--navy-800); margin-bottom: 0.5rem;">No articles published yet</h3>
                                <p style="color: var(--text-secondary);">Our experts are preparing new market insights. Check back soon.</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 2.5rem;">
                            <?php foreach ($posts as $post): ?>
                                <article style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.04); transition: transform 0.3s ease, box-shadow 0.3s ease; display: flex; flex-direction: column;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(0,0,0,0.04)';">
                                    
                                    <!-- Blog Image -->
                                    <div style="position: relative; height: 350px; overflow: hidden; background: var(--warm-200);">
                                        <?php $cover_image = $post['cover_image'] ?? ''; ?>
                                        <?php if (!empty($cover_image)): ?>
                                            <img src="<?php echo (strpos($cover_image, 'http') === 0) ? sanitize($cover_image) : asset_url($cover_image); ?>" alt="<?php echo sanitize($post['title']); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                        <?php else: ?>
                                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: var(--navy-800); color: var(--gold-200);">
                                                <i class="fas fa-image fa-3x"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div style="position: absolute; top: 1.5rem; left: 1.5rem; background: rgba(255,255,255,0.95); backdrop-filter: blur(4px); padding: 0.5rem 1rem; border-radius: 50px; font-size: 0.75rem; font-weight: 600; color: var(--navy-800); font-family: var(--font-family); text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                            Market Guide
                                        </div>
                                    </div>

                                    <!-- Blog Content -->
                                    <div style="padding: 2.5rem;">
                                        <div style="display: flex; align-items: center; justify-content: flex-start; gap: 1.5rem; margin-bottom: 1rem; font-size: 0.85rem; color: var(--text-muted); font-family: var(--font-family); text-transform: uppercase; letter-spacing: 0.05em;">
                                            <span><i class="far fa-calendar-alt" style="margin-right: 4px;"></i> <?php echo format_date($post['created_at']); ?></span>
                                            <span><i class="fas fa-user-circle" style="color: var(--gold-200); margin-right: 4px;"></i> <?php echo sanitize($post['author_name'] ?: 'Team iProply'); ?></span>
                                        </div>
                                        
                                        <h3 style="font-size: 1.75rem; font-family: var(--font-family); color: var(--navy-900); margin-bottom: 1rem; line-height: 1.3;">
                                            <a href="<?php echo base_url('blog-post.php?slug=' . urlencode($post['slug'])); ?>" style="text-decoration:none; color:inherit; transition: color 0.2s ease;" onmouseover="this.style.color='var(--gold-100)'" onmouseout="this.style.color='inherit'">
                                                <?php echo sanitize($post['title']); ?>
                                            </a>
                                        </h3>
                                        
                                        <p style="color: var(--text-secondary); font-size: 1.05rem; line-height: 1.7; margin-bottom: 1.5rem;">
                                            <?php echo sanitize($post['excerpt'] ?: truncate(strip_tags($post['content']), 180)); ?>
                                        </p>
                                        
                                        <a href="<?php echo base_url('blog-post.php?slug=' . urlencode($post['slug'])); ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; font-family: var(--font-family); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--navy-700); font-size: 0.85rem; padding: 10px 20px; border: 1px solid var(--border); border-radius: 50px; transition: all 0.3s ease;" onmouseover="this.style.background='var(--navy-900)'; this.style.color='white'; this.style.borderColor='var(--navy-900)'; this.querySelector('i').style.transform='translateX(4px)';" onmouseout="this.style.background='transparent'; this.style.color='var(--navy-700)'; this.style.borderColor='var(--border)'; this.querySelector('i').style.transform='translateX(0)';">
                                            Read Article <i class="fas fa-arrow-right" style="transition: transform 0.2s ease;"></i>
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div style="display:flex; justify-content:center; gap:0.5rem; margin-top:4rem;">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php
                                    $blogPaginationQuery['page'] = $i;
                                    $pageHref = base_url('blog.php?' . http_build_query($blogPaginationQuery));
                                    ?>
                                    <a href="<?php echo sanitize($pageHref); ?>"
                                       style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-family: var(--font-family); font-weight: 600; font-size: 0.95rem; transition: all 0.2s ease; <?php echo $i === $page ? 'background: var(--navy-800); color: white; box-shadow: 0 4px 15px rgba(4,20,39,0.2);' : 'background: white; color: var(--navy-800); border: 1px solid var(--border);'; ?>"
                                       onmouseover="<?php if($i !== $page) echo "this.style.borderColor='var(--gold-200)'; this.style.color='var(--gold-200)';"; ?>"
                                       onmouseout="<?php if($i !== $page) echo "this.style.borderColor='var(--border)'; this.style.color='var(--navy-800)';"; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Right Side: Sidebar (30%) -->
                <aside style="display: flex; flex-direction: column; gap: 2.5rem; position: sticky; top: 120px;">
                    
                    <!-- Search Widget -->
                    <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
                        <h4 style="font-family: var(--font-family); font-size: 1.25rem; color: var(--navy-900); margin-bottom: 1rem;">Search Insights</h4>
                        <form action="<?php echo base_url('blog.php'); ?>" method="GET" style="position: relative;">
                            <input type="text" name="q" value="<?php echo sanitize($searchQ); ?>" placeholder="Search titles &amp; topics..." style="width: 100%; padding: 12px 16px; padding-right: 45px; border: 1px solid var(--border); border-radius: 8px; font-family: var(--font-family); font-size: 0.95rem; outline: none; transition: border-color 0.3s ease;" onfocus="this.style.borderColor='var(--gold-200)'" onblur="this.style.borderColor='var(--border)'">
                            <button type="submit" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: var(--navy-700); cursor: pointer;">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Topic shortcuts (keyword search) -->
                    <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
                        <h4 style="font-family: var(--font-family); font-size: 1.25rem; color: var(--navy-900); margin-bottom: 0.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--warm-200);">Popular topics</h4>
                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 0 0 1rem 0;">Quick keyword searches — posts vary as we publish.</p>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <?php
                            $topicShortcuts = [
                                'Buying' => 'buying',
                                'Selling' => 'selling',
                                'Renting' => 'rent',
                                'Market trends' => 'market',
                            ];
                            foreach ($topicShortcuts as $label => $qq):
                                $topicHref = base_url('blog.php?' . http_build_query(['q' => $qq]));
                            ?>
                            <li style="margin-bottom: 0.65rem;">
                                <a href="<?php echo sanitize($topicHref); ?>" style="color: var(--navy-700); text-decoration: none; font-size: 0.95rem; border-bottom: 1px solid transparent; transition: border-color 0.2s ease;" onmouseover="this.style.borderBottomColor='var(--gold-200)'" onmouseout="this.style.borderBottomColor='transparent'"><?php echo sanitize($label); ?></a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Recent Posts Widget -->
                    <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
                        <h4 style="font-family: var(--font-family); font-size: 1.25rem; color: var(--navy-900); margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--warm-200);">Recent Articles</h4>
                        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                            <?php foreach($recentPosts as $rpost): ?>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <div style="width: 70px; height: 70px; border-radius: 8px; overflow: hidden; flex-shrink: 0; background: var(--warm-200);">
                                    <?php $rpost_cover = $rpost['cover_image'] ?? ''; ?>
                                    <?php if (!empty($rpost_cover)): ?>
                                        <img src="<?php echo (strpos($rpost_cover, 'http') === 0) ? sanitize($rpost_cover) : asset_url($rpost_cover); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fas fa-image" style="color: var(--gold-200); display: flex; align-items: center; justify-content: center; height: 100%;"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p style="font-size: 0.7rem; color: var(--text-muted); font-family: var(--font-family); text-transform: uppercase; margin-bottom: 0.25rem;"><?php echo format_date($rpost['created_at']); ?></p>
                                    <h5 style="font-family: var(--font-family); font-size: 0.95rem; line-height: 1.3; margin: 0;">
                                        <a href="<?php echo base_url('blog-post.php?slug=' . urlencode($rpost['slug'])); ?>" style="color: var(--navy-900); text-decoration: none; transition: color 0.2s ease;" onmouseover="this.style.color='var(--gold-100)'" onmouseout="this.style.color='var(--navy-900)'">
                                            <?php echo sanitize($rpost['title']); ?>
                                        </a>
                                    </h5>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Newsletter Widget -->
                    <div style="background: linear-gradient(135deg, var(--navy-900) 0%, var(--navy-700) 100%); padding: 2.5rem 2rem; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); color: white; text-align: center;">
                        <i class="far fa-envelope-open" style="font-size: 2.5rem; color: var(--gold-200); margin-bottom: 1rem;"></i>
                        <h4 style="font-family: var(--font-family); font-size: 1.5rem; margin-bottom: 0.5rem;">Join Our Newsletter</h4>
                        <p style="font-size: 0.9rem; color: rgba(255,255,255,0.8); margin-bottom: 1.5rem;">Get the latest real estate market insights directly to your inbox.</p>
                        <form action="<?php echo base_url('subscribe.php'); ?>" method="POST">
                            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generate_csrf_token(); ?>">
                            <label for="blog-newsletter-email" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;">Email address</label>
                            <input id="blog-newsletter-email" type="email" name="email" required placeholder="Your email address" style="width: 100%; padding: 12px 16px; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; font-family: var(--font-family); font-size: 0.95rem; background: rgba(255,255,255,0.1); color: white; outline: none; margin-bottom: 1rem;">
                            <button type="submit" style="width: 100%; padding: 12px; border: none; border-radius: 8px; background: linear-gradient(135deg, var(--gold-100) 0%, var(--gold-200) 100%); color: var(--navy-900); font-family: var(--font-family); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; cursor: pointer; transition: transform 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">Subscribe</button>
                        </form>
                    </div>

                    <!-- Promo Banner Widget -->
                    <div style="position: relative; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); background: var(--gold-100);">
                        <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=600&q=80" alt="Promo" style="width: 100%; height: 250px; object-fit: cover; opacity: 0.4;">
                        <div style="position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 2rem;">
                            <h4 style="font-family: var(--font-family); font-size: 1.5rem; color: var(--navy-900); margin-bottom: 0.5rem; font-weight: 700;">Find Your Dream Home</h4>
                            <p style="font-size: 0.9rem; color: var(--navy-800); margin-bottom: 1.5rem; font-weight: 500;">Browse exclusive premium listings today.</p>
                            <a href="<?php echo base_url('listings.php'); ?>" style="padding: 10px 20px; background: var(--navy-900); color: white; text-decoration: none; font-family: var(--font-family); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; border-radius: 50px; font-weight: 600;">View Properties</a>
                        </div>
                    </div>

                </aside>
            </div>
        </div>
        
    </div>
</section>

<?php include 'partials/footer.php'; ?>
