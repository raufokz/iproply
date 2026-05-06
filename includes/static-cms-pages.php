<?php
/**
 * Fallback CMS page records when the `pages` table is empty or a slug has no published row.
 * Matches seed content in database.sql (INSERT INTO pages).
 */
if (!function_exists('get_static_cms_page')) {
    function get_static_cms_page($slug) {
        $slug = strtolower(trim((string) $slug));

        static $bySlug;

        if ($bySlug === null) {
            $bySlug = [];

            foreach (static_cms_seed_pages() as $row) {
                $bySlug[$row['slug']] = $row;
            }
        }

        return $bySlug[$slug] ?? null;
    }

    /** @return array<int, array<string, mixed>> */
    function static_cms_seed_pages() {
        return [
            [
                'title' => 'About iProply',
                'slug' => 'about',
                'content' => "H2: A trusted real estate platform\n"
                    . "- iProply helps buyers and renters discover properties with confidence\n"
                    . "- We support agents with tools to manage listings and respond quickly\n\n"
                    . "H2: What you can do on iProply\n"
                    . "- Browse listings for sale and rent\n"
                    . "- Filter by location, price, and property type\n"
                    . "- Contact agents directly from listing pages\n\n"
                    . "H2: Our commitment\n"
                    . "- Clear, consistent information\n"
                    . "- Professional presentation across the site\n"
                    . "- Fair housing and equal opportunity principles",
                'meta_title' => 'About iProply | iProply',
                'meta_description' => 'Learn about iProply, a premium platform connecting buyers, renters, and agents with a focus on clarity, trust, and usability.',
            ],
            [
                'title' => 'Become an Agent',
                'slug' => 'become-agent',
                'content' => "H2: Grow your business with iProply\n"
                    . "- Premium listing exposure across the iProply marketplace\n"
                    . "- Lead and inquiry management tools built for speed\n"
                    . "- A polished agent profile to showcase your expertise\n\n"
                    . "H2: What you get\n"
                    . "- Centralized inbox for buyer and renter inquiries\n"
                    . "- Listing tools to publish, update, and manage your inventory\n"
                    . "- Performance insights to help you improve conversion\n\n"
                    . "H2: Who this is for\n"
                    . "- Licensed agents and broker associates\n"
                    . "- Teams looking to scale lead flow and operations\n"
                    . "- Independent agents building a premium brand\n\n"
                    . "H2: How to get started\n"
                    . "- Create your agent account and complete your profile\n"
                    . "- Add your first listing (sale or rent)\n"
                    . "- Respond quickly to inquiries to improve ranking and trust",
                'meta_title' => 'Become an Agent | iProply',
                'meta_description' => 'Join iProply as a real estate agent and grow your business with modern tools, premium exposure, and streamlined inquiry management.',
            ],
            [
                'title' => 'Partner With Us',
                'slug' => 'partners',
                'content' => "H2: Partnerships that create better home experiences\n"
                    . "- Builders and developers: showcase new communities and pre-construction inventory\n"
                    . "- Lenders: connect with qualified borrowers at the right moment\n"
                    . "- Home services: movers, staging, inspections, and repairs\n\n"
                    . "H2: What we offer\n"
                    . "- Sponsored placements and featured packages\n"
                    . "- Co-marketing opportunities and content partnerships\n"
                    . "- Dedicated support for onboarding and measurement\n\n"
                    . "H2: Get in touch\n"
                    . "- Use our Contact page to request a partnership conversation\n"
                    . "- Include your company name, service area, and partnership goals",
                'meta_title' => 'Partner With iProply | iProply',
                'meta_description' => 'Partner with iProply to reach motivated homebuyers and renters through sponsorships, co-marketing, and featured placements.',
            ],
            [
                'title' => 'Careers',
                'slug' => 'careers',
                'content' => "H2: Work with purpose\n"
                    . "- Help people find homes with confidence\n"
                    . "- Build products that make real estate simpler and more transparent\n\n"
                    . "H2: How we hire\n"
                    . "- Application review\n"
                    . "- Role-specific interview loop\n"
                    . "- Team conversation and references\n\n"
                    . "H2: What we value\n"
                    . "- Customer obsession and clear communication\n"
                    . "- Ownership and continuous improvement\n"
                    . "- Inclusion and respect\n\n"
                    . "H2: Interested?\n"
                    . "- Reach out via the Contact page with your resume and the role you are pursuing",
                'meta_title' => 'Careers | iProply',
                'meta_description' => 'Explore careers at iProply and help build a modern real estate experience with a customer-first, high-trust team.',
            ],
            [
                'title' => 'Why iProply?',
                'slug' => 'why-iproply',
                'content' => "H2: A premium real estate experience\n"
                    . "- Curated listings with clear details and high-quality media\n"
                    . "- Fast search, filters, and saved favorites\n"
                    . "- Trusted agents and responsive inquiry workflows\n\n"
                    . "H2: Built for buyers, renters, and agents\n"
                    . "- Buyers and renters: find the right home faster\n"
                    . "- Agents: manage listings and messages in one place\n"
                    . "- Admin tools: keep content and quality consistent\n\n"
                    . "H2: Our standards\n"
                    . "- Transparency in listing information\n"
                    . "- Fair housing commitment\n"
                    . "- Professional design and accessibility-first UX",
                'meta_title' => 'Why iProply | iProply',
                'meta_description' => 'Learn why iProply is a modern, premium real estate platform for buyers, renters, and agents with a focus on transparency and usability.',
            ],
            [
                'title' => 'Our Story',
                'slug' => 'our-story',
                'content' => "H2: Our mission\n"
                    . "- Make real estate simpler, more transparent, and more human\n\n"
                    . "H2: What we do\n"
                    . "- Connect buyers and renters with quality listings\n"
                    . "- Empower agents with modern tools and insights\n"
                    . "- Maintain a consistent standard of presentation across the platform\n\n"
                    . "H2: Where we’re headed\n"
                    . "- Better search and richer listing details\n"
                    . "- Faster agent-to-client communication\n"
                    . "- More guidance content for every step of the journey",
                'meta_title' => 'Our Story | iProply',
                'meta_description' => 'Read the iProply story and our mission to modernize real estate with tools that improve trust, clarity, and outcomes.',
            ],
            [
                'title' => 'Community Impact',
                'slug' => 'community-impact',
                'content' => "H2: Investing in communities\n"
                    . "- Supporting housing stability initiatives\n"
                    . "- Promoting education and financial literacy\n"
                    . "- Encouraging responsible development and local engagement\n\n"
                    . "H2: How we contribute\n"
                    . "- Volunteer programs and local partnerships\n"
                    . "- Donation matching during seasonal campaigns\n"
                    . "- Highlighting community-focused listings and resources",
                'meta_title' => 'Community Impact | iProply',
                'meta_description' => 'See how iProply supports communities through housing initiatives, partnerships, and responsible real estate practices.',
            ],
            [
                'title' => 'Diversity & Inclusion',
                'slug' => 'inclusion',
                'content' => "H2: Our commitment\n"
                    . "- We support equal housing opportunity and fair housing practices\n"
                    . "- We respect and celebrate the diversity of the communities we serve\n\n"
                    . "H2: What inclusion means to us\n"
                    . "- Building products that work for everyone\n"
                    . "- Reducing bias through clear standards and transparent processes\n"
                    . "- Creating a welcoming environment for clients, agents, and partners",
                'meta_title' => 'Diversity & Inclusion | iProply',
                'meta_description' => 'Learn about iProply’s commitment to diversity, inclusion, and fair housing across our platform and community.',
            ],
            [
                'title' => 'Press & Media',
                'slug' => 'press',
                'content' => "H2: Press inquiries\n"
                    . "- For press and media requests, please contact our team via the Contact page\n\n"
                    . "H2: Brand assets\n"
                    . "- Logos and brand guidelines are available upon request\n\n"
                    . "H2: Company overview\n"
                    . "- iProply is a premium real estate platform focused on clarity, trust, and usability for buyers, renters, and agents",
                'meta_title' => 'Press & Media | iProply',
                'meta_description' => 'Press and media resources for iProply, including inquiries and brand assets.',
            ],
            [
                'title' => 'Help Center',
                'slug' => 'help-center',
                'content' => "H2: Get help fast\n"
                    . "- Searching and filtering listings\n"
                    . "- Managing favorites and saved searches\n"
                    . "- Contacting an agent and responding to messages\n\n"
                    . "H2: Agents\n"
                    . "- Updating your profile\n"
                    . "- Publishing and managing listings\n"
                    . "- Responding to inquiries effectively\n\n"
                    . "H2: Still need help?\n"
                    . "- Reach out through the Contact page and we’ll respond as quickly as possible",
                'meta_title' => 'Help Center | iProply',
                'meta_description' => 'Find help with searching listings, contacting agents, managing your account, and using iProply’s tools.',
            ],
            [
                'title' => 'Market Reports',
                'slug' => 'market-reports',
                'content' => "H2: Market insights\n"
                    . "- Trend summaries by region\n"
                    . "- Pricing and inventory signals\n"
                    . "- Rental demand snapshots\n\n"
                    . "H2: How to use market reports\n"
                    . "- Compare neighborhoods and property types\n"
                    . "- Track pricing changes over time\n"
                    . "- Use insights to plan offers and timing\n\n"
                    . "H2: Disclaimer\n"
                    . "- Market data is provided for informational purposes and may not reflect real-time conditions",
                'meta_title' => 'Market Reports | iProply',
                'meta_description' => 'Market reports and real estate insights to help buyers, renters, and sellers understand trends and pricing signals.',
            ],
            [
                'title' => 'Mortgage Calculator',
                'slug' => 'mortgage-calculator',
                'content' => "H2: Estimate your monthly payment\n"
                    . "- Enter home price, down payment, interest rate, and term\n"
                    . "- Consider taxes, insurance, and HOA where applicable\n\n"
                    . "H2: Tips\n"
                    . "- Use conservative assumptions for taxes and insurance\n"
                    . "- Compare multiple rates and terms\n"
                    . "- Keep a buffer for maintenance and closing costs\n\n"
                    . "H2: Disclaimer\n"
                    . "- Estimates are for informational purposes and are not a loan offer or approval",
                'meta_title' => 'Mortgage Calculator | iProply',
                'meta_description' => 'Use a mortgage calculator to estimate monthly payments and plan your home budget with confidence.',
            ],
            [
                'title' => 'Advertise',
                'slug' => 'advertise',
                'content' => "H2: Reach motivated home shoppers\n"
                    . "- Featured placements for listings and partners\n"
                    . "- Sponsored content opportunities\n"
                    . "- Brand visibility across high-intent discovery surfaces\n\n"
                    . "H2: Options\n"
                    . "- Spotlight listings\n"
                    . "- Partner directory placement\n"
                    . "- Seasonal campaigns\n\n"
                    . "H2: Get started\n"
                    . "- Contact us with your goals, target market, and budget range",
                'meta_title' => 'Advertise | iProply',
                'meta_description' => 'Advertising options for real estate brands and partners to reach motivated buyers and renters on iProply.',
            ],
            [
                'title' => 'Terms of Use',
                'slug' => 'terms-of-use',
                'content' => "H2: Overview\n"
                    . "- These Terms of Use govern your access to and use of the iProply website and services\n"
                    . "- By using the site, you agree to these terms\n\n"
                    . "H2: Use of the site\n"
                    . "- Do not misuse the platform, attempt unauthorized access, or disrupt service\n"
                    . "- You are responsible for activity under your account\n\n"
                    . "H2: Listings and content\n"
                    . "- Listing information is provided by third parties and may change\n"
                    . "- We do not guarantee accuracy, completeness, or availability\n\n"
                    . "H2: Disclaimers\n"
                    . "- The site is provided “as is” without warranties\n"
                    . "- To the fullest extent permitted by law, we disclaim liability for damages arising from use\n\n"
                    . "H2: Contact\n"
                    . "- Questions about these terms can be sent through the Contact page",
                'meta_title' => 'Terms of Use | iProply',
                'meta_description' => 'Read iProply’s Terms of Use covering site access, acceptable use, listing information, and legal disclaimers.',
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => "H2: Overview\n"
                    . "- This Privacy Policy explains how iProply collects, uses, and shares information\n\n"
                    . "H2: Information we collect\n"
                    . "- Contact information you submit through forms\n"
                    . "- Account information for agents and administrators\n"
                    . "- Usage data to improve performance and reliability\n\n"
                    . "H2: How we use information\n"
                    . "- Provide and improve services\n"
                    . "- Respond to inquiries and support requests\n"
                    . "- Maintain security and prevent abuse\n\n"
                    . "H2: Sharing\n"
                    . "- We may share information with service providers who help operate the site\n"
                    . "- We do not sell personal information as part of normal operations\n\n"
                    . "H2: Your choices\n"
                    . "- You can request access, correction, or deletion via the Contact page\n\n"
                    . "H2: Contact\n"
                    . "- Privacy questions can be sent through the Contact page",
                'meta_title' => 'Privacy Policy | iProply',
                'meta_description' => 'Read iProply’s Privacy Policy covering the information we collect, how we use it, and your choices.',
            ],
            [
                'title' => 'Do Not Sell or Share My Personal Information',
                'slug' => 'do-not-sell',
                'content' => "H2: Your privacy choices\n"
                    . "- You may request that we do not sell or share your personal information where applicable\n\n"
                    . "H2: How to submit a request\n"
                    . "- Use the Contact page and include:\n"
                    . "- Your name and email address\n"
                    . "- The request type: “Do Not Sell or Share”\n"
                    . "- Any additional details needed to verify your request\n\n"
                    . "H2: Verification\n"
                    . "- We may need to verify your identity before completing the request\n\n"
                    . "H2: Note\n"
                    . "- This page is provided for informational purposes and does not create legal rights beyond what is provided by applicable law",
                'meta_title' => 'Do Not Sell or Share My Personal Information | iProply',
                'meta_description' => 'Submit a request regarding the sale or sharing of personal information, where applicable.',
            ],
            [
                'title' => 'Fair Housing',
                'slug' => 'fair-housing',
                'content' => "H2: Equal Housing Opportunity\n"
                    . "- iProply supports the Fair Housing Act and equal opportunity in housing\n\n"
                    . "H2: Prohibited discrimination\n"
                    . "- Housing-related discrimination may be prohibited based on protected characteristics under applicable law\n\n"
                    . "H2: Reporting concerns\n"
                    . "- If you believe a listing or interaction violates fair housing principles, contact us through the Contact page\n\n"
                    . "H2: Resources\n"
                    . "- For general fair housing information, you may also consult federal, state, and local fair housing agencies",
                'meta_title' => 'Fair Housing | iProply',
                'meta_description' => 'iProply is committed to equal housing opportunity and Fair Housing Act principles.',
            ],
            [
                'title' => 'Fair Housing Policy',
                'slug' => 'fair-housing-policy',
                'content' => "H2: Policy statement\n"
                    . "- iProply is committed to fair housing and equal opportunity\n\n"
                    . "H2: Our approach\n"
                    . "- We maintain clear platform standards for listings\n"
                    . "- We encourage accurate and non-discriminatory listing language\n"
                    . "- We provide pathways to report concerns\n\n"
                    . "H2: Platform enforcement\n"
                    . "- Content that violates policy may be removed\n"
                    . "- Accounts may be restricted for repeated violations\n\n"
                    . "H2: Contact\n"
                    . "- Use the Contact page to report concerns or request information",
                'meta_title' => 'Fair Housing Policy | iProply',
                'meta_description' => 'Read iProply’s Fair Housing Policy and our standards for listings, conduct, and enforcement.',
            ],
            [
                'title' => 'Texas: Info About Brokerage Services',
                'slug' => 'texas-ibs',
                'content' => "H2: Texas Information About Brokerage Services\n"
                    . "- This page provides a reference link for Texas consumers\n\n"
                    . "H2: Notice\n"
                    . "- Brokerage and consumer protection notices may be required for certain transactions\n"
                    . "- Please consult a licensed professional for guidance\n\n"
                    . "H2: Official resources\n"
                    . "- Texas Real Estate Commission (TREC): Consumer Protection Notice and related forms are available on the TREC website",
                'meta_title' => 'Texas Info About Brokerage Services | iProply',
                'meta_description' => 'Texas consumer information about brokerage services and related notices.',
            ],
            [
                'title' => 'Referral Network',
                'slug' => 'referral-network',
                'content' => "H2: Grow through trusted referrals\n"
                    . "- Send clients to colleagues when they are relocating or buying out of area\n"
                    . "- Earn and share referral fees responsibly under your broker’s guidance\n\n"
                    . "H2: How iProply helps\n"
                    . "- Profiles and messaging that make introductions easy\n"
                    . "- Coverage across major metros and growing markets\n\n"
                    . "H2: Get connected\n"
                    . "- Speak with our team via the Contact page to learn about referral opportunities",
                'meta_title' => 'Referral Network | iProply',
                'meta_description' => 'Connect with iProply’s referral network to help clients nationally while keeping relationships professional and compliant.',
            ],
        ];
    }
}
