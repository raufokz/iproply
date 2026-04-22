<?php
/**
 * iProply - Single Blog Post
 */
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Blog.php';

$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';
$blogModel = new Blog();
$post = $slug ? $blogModel->getBySlug($slug) : null;

if (!$post) {
    set_flash_message('error', 'Blog post not found.');
    redirect('blog.php');
}

$currentPage = 'blog';
$pageTitle = $post['title'];

// Fetch recent posts for sidebar
$recentPosts = $blogModel->getPublished(1, 4);

include 'partials/header.php';
?>

<!-- Post Hero Section -->
<section style="position: relative; padding: 160px 0 80px; background: var(--navy-900); color: white; overflow: hidden;">
    <?php $post_cover = $post['cover_image'] ?? ''; ?>
    <?php if (!empty($post_cover)): ?>
        <div style="position: absolute; inset: 0; z-index: 0;">
            <img src="<?php echo (strpos($post_cover, 'http') === 0) ? sanitize($post_cover) : asset_url($post_cover); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.35;">
            <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(4,20,39,0.8) 0%, rgba(4,20,39,1) 100%);"></div>
        </div>
    <?php endif; ?>
    
    <div class="container" style="position: relative; z-index: 1; max-width: 900px; text-align: center;">
        <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; margin-bottom: 1.5rem; font-family: 'Jost', sans-serif; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--gold-200);">
            <span><i class="far fa-calendar-alt"></i> <?php echo format_date($post['created_at']); ?></span>
            <span>|</span>
            <span><i class="far fa-user"></i> <?php echo sanitize($post['author_name'] ?: 'Team iProply'); ?></span>
        </div>
        
        <h1 style="font-size: clamp(2.5rem, 5vw, 4rem); font-weight: 700; font-family: 'Playfair Display', serif; line-height: 1.2; margin-bottom: 2rem;">
            <?php echo sanitize($post['title']); ?>
        </h1>
        
        <a href="<?php echo base_url('blog.php'); ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 12px 24px; border: 1px solid rgba(255,255,255,0.2); border-radius: 50px; color: white; font-family: 'Jost', sans-serif; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.1)'; this.style.borderColor='rgba(255,255,255,0.4)';" onmouseout="this.style.background='transparent'; this.style.borderColor='rgba(255,255,255,0.2)';">
            <i class="fas fa-arrow-left"></i> Back to Insights
        </a>
    </div>
</section>

<!-- Content Section -->
<section style="padding: 5rem 0; background: var(--cream);">
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
                    <article style="background: white; padding: clamp(2rem, 5vw, 4rem); border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.03);">
                        
                        <div class="blog-content" style="font-size: 1.1rem; line-height: 1.8; color: var(--navy-700);">
                            <?php 
                            // Format the content better
                            $content = sanitize($post['content']);
                            $content = preg_replace('/H1:\s*(.*?)(\n|$)/', '<h2 style="font-family: \'Playfair Display\', serif; font-size: 2rem; color: var(--navy-900); margin: 2.5rem 0 1.5rem;">$1</h2>', $content);
                            $content = preg_replace('/H2:\s*(.*?)(\n|$)/', '<h3 style="font-family: \'Playfair Display\', serif; font-size: 1.75rem; color: var(--navy-900); margin: 2rem 0 1rem;">$1</h3>', $content);
                            $content = preg_replace('/H3:\s*(.*?)(\n|$)/', '<h4 style="font-family: \'Jost\', sans-serif; font-size: 1.25rem; font-weight: 600; color: var(--navy-800); margin: 1.5rem 0 1rem;">$1</h4>', $content);
                            $content = preg_replace('/Pro Tip:\s*(.*?)(\n|$)/', '<div style="background: var(--warm-100); border-left: 4px solid var(--gold-200); padding: 1.5rem; margin: 2rem 0; border-radius: 0 12px 12px 0;"><strong style="font-family: \'Jost\', sans-serif; color: var(--navy-900); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 0.5rem;"><i class="fas fa-lightbulb" style="color: var(--gold-200);"></i> Pro Tip</strong>$1</div>', $content);
                            
                            // Parse "Image: [caption]" placeholders
                            $inlineImages = [
                                'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1000&q=80',
                                'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1000&q=80',
                                'https://images.unsplash.com/photo-1600607687931-cebf0052065e?auto=format&fit=crop&w=1000&q=80',
                                'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1000&q=80',
                                'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1000&q=80'
                            ];
                            
                            $content = preg_replace_callback('/Image:\s*(.*?)(\n|$)/i', function($matches) use ($inlineImages) {
                                static $imgIndex = 0;
                                $caption = trim($matches[1]);
                                $imgSrc = $inlineImages[$imgIndex % count($inlineImages)];
                                $imgIndex++;
                                
                                $figure = '<figure style="margin: 3rem 0; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">';
                                $figure .= '<img src="' . $imgSrc . '" alt="' . htmlspecialchars($caption) . '" style="width: 100%; height: auto; display: block;">';
                                if (!empty($caption)) {
                                    $figure .= '<figcaption style="padding: 1rem; background: var(--warm-100); color: var(--text-secondary); font-size: 0.9rem; text-align: center; font-family: \'Jost\', sans-serif;">' . htmlspecialchars($caption) . '</figcaption>';
                                }
                                $figure .= '</figure>';
                                return $figure;
                            }, $content);

                            $content = nl2br($content);
                            echo $content;
                            ?>
                        </div>
                        
                        <!-- Author Box -->
                        <div style="margin-top: 4rem; padding-top: 3rem; border-top: 1px solid var(--border); display: flex; align-items: center; gap: 1.5rem;">
                            <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--warm-200); display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--navy-700);">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div>
                                <h4 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--navy-900); margin-bottom: 0.25rem;">Written by <?php echo sanitize($post['author_name'] ?: 'Team iProply'); ?></h4>
                                <p style="color: var(--text-secondary); font-size: 0.95rem; margin: 0;">Real Estate Market Analyst & Investment Specialist at iProply.</p>
                            </div>
                        </div>
                        
                    </article>
                </div>

                <!-- Right Side: Sidebar (30%) -->
                <aside style="display: flex; flex-direction: column; gap: 2.5rem; position: sticky; top: 120px;">
                    
                    <!-- Search Widget -->
                    <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
                        <h4 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--navy-900); margin-bottom: 1rem;">Search Insights</h4>
                        <form action="blog.php" method="GET" style="position: relative;">
                            <input type="text" name="q" placeholder="Type keyword..." style="width: 100%; padding: 12px 16px; padding-right: 45px; border: 1px solid var(--border); border-radius: 8px; font-family: 'Jost', sans-serif; font-size: 0.95rem; outline: none; transition: border-color 0.3s ease;" onfocus="this.style.borderColor='var(--gold-200)'" onblur="this.style.borderColor='var(--border)'">
                            <button type="submit" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: var(--navy-700); cursor: pointer;">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Categories Widget -->
                    <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
                        <h4 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--navy-900); margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--warm-200);">Categories</h4>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <?php
                            $categories = ['Buying Guides' => 12, 'Selling Tips' => 8, 'Market Trends' => 5, 'Investment' => 9, 'Home Improvement' => 4];
                            foreach($categories as $cat => $count):
                            ?>
                            <li style="margin-bottom: 0.75rem; border-bottom: 1px dashed var(--warm-200); padding-bottom: 0.75rem;">
                                <a href="#" style="display: flex; justify-content: space-between; color: var(--text-secondary); text-decoration: none; font-size: 0.95rem; transition: color 0.2s ease;" onmouseover="this.style.color='var(--navy-800)'" onmouseout="this.style.color='var(--text-secondary)'">
                                    <span><?php echo $cat; ?></span>
                                    <span style="background: var(--warm-100); padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; color: var(--navy-700); font-family: 'Jost', sans-serif;"><?php echo $count; ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Recent Posts Widget -->
                    <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
                        <h4 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--navy-900); margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--warm-200);">Recent Articles</h4>
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
                                    <p style="font-size: 0.7rem; color: var(--text-muted); font-family: 'Jost', sans-serif; text-transform: uppercase; margin-bottom: 0.25rem;"><?php echo format_date($rpost['created_at']); ?></p>
                                    <h5 style="font-family: 'Playfair Display', serif; font-size: 0.95rem; line-height: 1.3; margin: 0;">
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
                        <h4 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; margin-bottom: 0.5rem;">Join Our Newsletter</h4>
                        <p style="font-size: 0.9rem; color: rgba(255,255,255,0.8); margin-bottom: 1.5rem;">Get the latest real estate market insights directly to your inbox.</p>
                        <form action="#" method="POST">
                            <input type="email" placeholder="Your Email Address" style="width: 100%; padding: 12px 16px; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; font-family: 'Jost', sans-serif; font-size: 0.95rem; background: rgba(255,255,255,0.1); color: white; outline: none; margin-bottom: 1rem;">
                            <button type="submit" style="width: 100%; padding: 12px; border: none; border-radius: 8px; background: linear-gradient(135deg, var(--gold-100) 0%, var(--gold-200) 100%); color: var(--navy-900); font-family: 'Jost', sans-serif; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; cursor: pointer; transition: transform 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">Subscribe</button>
                        </form>
                    </div>

                    <!-- Promo Banner Widget -->
                    <div style="position: relative; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); background: var(--gold-100);">
                        <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=600&q=80" alt="Promo" style="width: 100%; height: 250px; object-fit: cover; opacity: 0.4;">
                        <div style="position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 2rem;">
                            <h4 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--navy-900); margin-bottom: 0.5rem; font-weight: 700;">Find Your Dream Home</h4>
                            <p style="font-size: 0.9rem; color: var(--navy-800); margin-bottom: 1.5rem; font-weight: 500;">Browse exclusive premium listings today.</p>
                            <a href="<?php echo base_url('listings.php'); ?>" style="padding: 10px 20px; background: var(--navy-900); color: white; text-decoration: none; font-family: 'Jost', sans-serif; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; border-radius: 50px; font-weight: 600;">View Properties</a>
                        </div>
                    </div>

                </aside>
            </div>
        </div>
    </div>
</section>

<?php include 'partials/footer.php'; ?>
