-- iProply - Pages CMS Migration (Non-destructive)
-- Run this on an existing database to add CMS pages + newsletter subscribers.

-- ============================================
-- TABLE: pages
-- ============================================
CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    published_at DATETIME NULL,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    meta_keywords TEXT NULL,
    show_in_footer TINYINT(1) NOT NULL DEFAULT 0,
    footer_section VARCHAR(50) NULL,
    footer_order INT NOT NULL DEFAULT 0,
    created_by INT NULL,
    updated_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: newsletter_subscribers
-- ============================================
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEED: Standard real estate content pages
-- Insert only if the slug does not exist.
-- ============================================

-- Join Us
INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Become an Agent', 'become-agent',
       'H2: Grow your business with iProply\n- Premium listing exposure across the iProply marketplace\n- Lead and inquiry management tools built for speed\n- A polished agent profile to showcase your expertise\n\nH2: What you get\n- Centralized inbox for buyer and renter inquiries\n- Listing tools to publish, update, and manage your inventory\n- Performance insights to help you improve conversion\n\nH2: Who this is for\n- Licensed agents and broker associates\n- Teams looking to scale lead flow and operations\n- Independent agents building a premium brand\n\nH2: How to get started\n- Create your agent account and complete your profile\n- Add your first listing (sale or rent)\n- Respond quickly to inquiries to improve ranking and trust',
       'published', NOW(),
       'Become an Agent | iProply', 'Join iProply as a real estate agent and grow your business with modern tools, premium exposure, and streamlined inquiry management.',
       1, 'join', 10
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'become-agent');

INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Partner With Us', 'partners',
       'H2: Partnerships that create better home experiences\n- Builders and developers: showcase new communities and pre-construction inventory\n- Lenders: connect with qualified borrowers at the right moment\n- Home services: movers, staging, inspections, and repairs\n\nH2: What we offer\n- Sponsored placements and featured packages\n- Co-marketing opportunities and content partnerships\n- Dedicated support for onboarding and measurement\n\nH2: Get in touch\n- Use our Contact page to request a partnership conversation\n- Include your company name, service area, and partnership goals',
       'published', NOW(),
       'Partner With iProply | iProply', 'Partner with iProply to reach motivated homebuyers and renters through sponsorships, co-marketing, and featured placements.',
       1, 'join', 20
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'partners');

INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Careers', 'careers',
       'H2: Work with purpose\n- Help people find homes with confidence\n- Build products that make real estate simpler and more transparent\n\nH2: How we hire\n- Application review\n- Role-specific interview loop\n- Team conversation and references\n\nH2: What we value\n- Customer obsession and clear communication\n- Ownership and continuous improvement\n- Inclusion and respect\n\nH2: Interested?\n- Reach out via the Contact page with your resume and the role you are pursuing',
       'published', NOW(),
       'Careers | iProply', 'Explore careers at iProply and help build a modern real estate experience with a customer-first, high-trust team.',
       1, 'join', 30
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'careers');

-- About
INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'About iProply', 'about',
       'H2: A trusted real estate platform\n- iProply helps buyers and renters discover properties with confidence\n- We support agents with tools to manage listings and respond quickly\n\nH2: What you can do on iProply\n- Browse listings for sale and rent\n- Filter by location, price, and property type\n- Contact agents directly from listing pages\n\nH2: Our commitment\n- Clear, consistent information\n- Professional presentation across the site\n- Fair housing and equal opportunity principles',
       'published', NOW(),
       'About iProply | iProply', 'Learn about iProply, a premium platform connecting buyers, renters, and agents with a focus on clarity, trust, and usability.',
       0, 'about', 0
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'about');

INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Why iProply?', 'why-iproply',
       'H2: A premium real estate experience\n- Curated listings with clear details and high-quality media\n- Fast search, filters, and saved favorites\n- Trusted agents and responsive inquiry workflows\n\nH2: Built for buyers, renters, and agents\n- Buyers and renters: find the right home faster\n- Agents: manage listings and messages in one place\n- Admin tools: keep content and quality consistent\n\nH2: Our standards\n- Transparency in listing information\n- Fair housing commitment\n- Professional design and accessibility-first UX',
       'published', NOW(),
       'Why iProply | iProply', 'Learn why iProply is a modern, premium real estate platform for buyers, renters, and agents with a focus on transparency and usability.',
       1, 'about', 10
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'why-iproply');

INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Our Story', 'our-story',
       'H2: Our mission\n- Make real estate simpler, more transparent, and more human\n\nH2: What we do\n- Connect buyers and renters with quality listings\n- Empower agents with modern tools and insights\n- Maintain a consistent standard of presentation across the platform\n\nH2: Where we’re headed\n- Better search and richer listing details\n- Faster agent-to-client communication\n- More guidance content for every step of the journey',
       'published', NOW(),
       'Our Story | iProply', 'Read the iProply story and our mission to modernize real estate with tools that improve trust, clarity, and outcomes.',
       1, 'about', 20
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'our-story');

INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Community Impact', 'community-impact',
       'H2: Investing in communities\n- Supporting housing stability initiatives\n- Promoting education and financial literacy\n- Encouraging responsible development and local engagement\n\nH2: How we contribute\n- Volunteer programs and local partnerships\n- Donation matching during seasonal campaigns\n- Highlighting community-focused listings and resources',
       'published', NOW(),
       'Community Impact | iProply', 'See how iProply supports communities through housing initiatives, partnerships, and responsible real estate practices.',
       1, 'about', 30
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'community-impact');

INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Diversity & Inclusion', 'inclusion',
       'H2: Our commitment\n- We support equal housing opportunity and fair housing practices\n- We respect and celebrate the diversity of the communities we serve\n\nH2: What inclusion means to us\n- Building products that work for everyone\n- Reducing bias through clear standards and transparent processes\n- Creating a welcoming environment for clients, agents, and partners',
       'published', NOW(),
       'Diversity & Inclusion | iProply', 'Learn about iProply’s commitment to diversity, inclusion, and fair housing across our platform and community.',
       1, 'about', 40
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'inclusion');

INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Press & Media', 'press',
       'H2: Press inquiries\n- For press and media requests, please contact our team via the Contact page\n\nH2: Brand assets\n- Logos and brand guidelines are available upon request\n\nH2: Company overview\n- iProply is a premium real estate platform focused on clarity, trust, and usability for buyers, renters, and agents',
       'published', NOW(),
       'Press & Media | iProply', 'Press and media resources for iProply, including inquiries and brand assets.',
       1, 'about', 50
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'press');

-- Resources
INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Help Center', 'help-center',
       'H2: Get help fast\n- Searching and filtering listings\n- Managing favorites and saved searches\n- Contacting an agent and responding to messages\n\nH2: Agents\n- Updating your profile\n- Publishing and managing listings\n- Responding to inquiries effectively\n\nH2: Still need help?\n- Reach out through the Contact page and we’ll respond as quickly as possible',
       'published', NOW(),
       'Help Center | iProply', 'Find help with searching listings, contacting agents, managing your account, and using iProply’s tools.',
       1, 'resources', 20
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'help-center');

INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Market Reports', 'market-reports',
       'H2: Market insights\n- Trend summaries by region\n- Pricing and inventory signals\n- Rental demand snapshots\n\nH2: How to use market reports\n- Compare neighborhoods and property types\n- Track pricing changes over time\n- Use insights to plan offers and timing\n\nH2: Disclaimer\n- Market data is provided for informational purposes and may not reflect real-time conditions',
       'published', NOW(),
       'Market Reports | iProply', 'Market reports and real estate insights to help buyers, renters, and sellers understand trends and pricing signals.',
       1, 'resources', 30
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'market-reports');

INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Mortgage Calculator', 'mortgage-calculator',
       'H2: Estimate your monthly payment\n- Enter home price, down payment, interest rate, and term\n- Consider taxes, insurance, and HOA where applicable\n\nH2: Tips\n- Use conservative assumptions for taxes and insurance\n- Compare multiple rates and terms\n- Keep a buffer for maintenance and closing costs\n\nH2: Disclaimer\n- Estimates are for informational purposes and are not a loan offer or approval',
       'published', NOW(),
       'Mortgage Calculator | iProply', 'Use a mortgage calculator to estimate monthly payments and plan your home budget with confidence.',
       1, 'resources', 40
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'mortgage-calculator');

INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Advertise', 'advertise',
       'H2: Reach motivated home shoppers\n- Featured placements for listings and partners\n- Sponsored content opportunities\n- Brand visibility across high-intent discovery surfaces\n\nH2: Options\n- Spotlight listings\n- Partner directory placement\n- Seasonal campaigns\n\nH2: Get started\n- Contact us with your goals, target market, and budget range',
       'published', NOW(),
       'Advertise | iProply', 'Advertising options for real estate brands and partners to reach motivated buyers and renters on iProply.',
       1, 'resources', 50
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'advertise');

-- Legal
INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Terms of Use', 'terms-of-use',
       'H2: Overview\n- These Terms of Use govern your access to and use of the iProply website and services\n- By using the site, you agree to these terms\n\nH2: Use of the site\n- Do not misuse the platform, attempt unauthorized access, or disrupt service\n- You are responsible for activity under your account\n\nH2: Listings and content\n- Listing information is provided by third parties and may change\n- We do not guarantee accuracy, completeness, or availability\n\nH2: Disclaimers\n- The site is provided “as is” without warranties\n- To the fullest extent permitted by law, we disclaim liability for damages arising from use\n\nH2: Contact\n- Questions about these terms can be sent through the Contact page',
       'published', NOW(),
       'Terms of Use | iProply', 'Read iProply’s Terms of Use covering site access, acceptable use, listing information, and legal disclaimers.',
       0, 'legal', 10
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'terms-of-use');

INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Privacy Policy', 'privacy-policy',
       'H2: Overview\n- This Privacy Policy explains how iProply collects, uses, and shares information\n\nH2: Information we collect\n- Contact information you submit through forms\n- Account information for agents and administrators\n- Usage data to improve performance and reliability\n\nH2: How we use information\n- Provide and improve services\n- Respond to inquiries and support requests\n- Maintain security and prevent abuse\n\nH2: Sharing\n- We may share information with service providers who help operate the site\n- We do not sell personal information as part of normal operations\n\nH2: Your choices\n- You can request access, correction, or deletion via the Contact page\n\nH2: Contact\n- Privacy questions can be sent through the Contact page',
       'published', NOW(),
       'Privacy Policy | iProply', 'Read iProply’s Privacy Policy covering the information we collect, how we use it, and your choices.',
       0, 'legal', 20
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'privacy-policy');

INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Do Not Sell or Share My Personal Information', 'do-not-sell',
       'H2: Your privacy choices\n- You may request that we do not sell or share your personal information where applicable\n\nH2: How to submit a request\n- Use the Contact page and include:\n- Your name and email address\n- The request type: “Do Not Sell or Share”\n- Any additional details needed to verify your request\n\nH2: Verification\n- We may need to verify your identity before completing the request\n\nH2: Note\n- This page is provided for informational purposes and does not create legal rights beyond what is provided by applicable law',
       'published', NOW(),
       'Do Not Sell or Share My Personal Information | iProply', 'Submit a request regarding the sale or sharing of personal information, where applicable.',
       0, 'legal', 30
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'do-not-sell');

INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Fair Housing', 'fair-housing',
       'H2: Equal Housing Opportunity\n- iProply supports the Fair Housing Act and equal opportunity in housing\n\nH2: Prohibited discrimination\n- Housing-related discrimination may be prohibited based on protected characteristics under applicable law\n\nH2: Reporting concerns\n- If you believe a listing or interaction violates fair housing principles, contact us through the Contact page\n\nH2: Resources\n- For general fair housing information, you may also consult federal, state, and local fair housing agencies',
       'published', NOW(),
       'Fair Housing | iProply', 'iProply is committed to equal housing opportunity and Fair Housing Act principles.',
       0, 'legal', 40
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'fair-housing');

INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Fair Housing Policy', 'fair-housing-policy',
       'H2: Policy statement\n- iProply is committed to fair housing and equal opportunity\n\nH2: Our approach\n- We maintain clear platform standards for listings\n- We encourage accurate and non-discriminatory listing language\n- We provide pathways to report concerns\n\nH2: Platform enforcement\n- Content that violates policy may be removed\n- Accounts may be restricted for repeated violations\n\nH2: Contact\n- Use the Contact page to report concerns or request information',
       'published', NOW(),
       'Fair Housing Policy | iProply', 'Read iProply’s Fair Housing Policy and our standards for listings, conduct, and enforcement.',
       0, 'legal', 50
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'fair-housing-policy');

INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order)
SELECT 'Texas: Info About Brokerage Services', 'texas-ibs',
       'H2: Texas Information About Brokerage Services\n- This page provides a reference link for Texas consumers\n\nH2: Notice\n- Brokerage and consumer protection notices may be required for certain transactions\n- Please consult a licensed professional for guidance\n\nH2: Official resources\n- Texas Real Estate Commission (TREC): Consumer Protection Notice and related forms are available on the TREC website',
       'published', NOW(),
       'Texas Info About Brokerage Services | iProply', 'Texas consumer information about brokerage services and related notices.',
       0, 'legal', 60
WHERE NOT EXISTS (SELECT 1 FROM pages WHERE slug = 'texas-ibs');
