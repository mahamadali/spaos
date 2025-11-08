<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Blog; // make sure this path is correct
use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;

class BlogsTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        // Optionally truncate the table before seeding
        Blog::truncate();

        $blogs = [
            [
                'auther_id'   => 3,
                'title'       => '5 Salon Management Tips to Boost Your Business',
                'status'      => 1,
                'image' => 'blog-1.jpeg',
                'description' => '<div role="listitem">
    <div class="">
        <div id="post_kzijp7josjb598sc5rhumq5juy" class="a11y__section post other--root current--user post--hovered" tabindex="0" aria-label="At 3:48 PM Monday, February 24, denish wrote, 1. 5 Salon Management Tips to Boost Your Business in 2024
Published on: Jan 15, 2024

Running a successful salon requires more than just great styling skills&mdash;it&rsquo;s about efficient management. Here are five expert tips to streamline operations and grow your salon business:

1️⃣ Automate appointment scheduling 📅
2️⃣ Offer loyalty programs &amp; discounts 🎁
3️⃣ Track staff performance &amp; optimize schedules 👩&zwj;💼
4️⃣ Improve customer engagement with personalized services 💬
5️⃣ Use data-driven insights to enhance business decisions 📊" data-testid="postView">
            <div class="post__content " role="application" data-testid="postContent">
                <div id="kzijp7josjb598sc5rhumq5juy_message" class="post__body post--edited">
                    <div class="AutoHeight">
                        <div class="post-message post-message--collapsed">
                            <div class="post-message__text-container">
                                <div id="postMessageText_kzijp7josjb598sc5rhumq5juy" class="post-message__text" dir="auto" tabindex="0">
                                    <p><span style="text-decoration: underline;"><strong>Published on: Jan 15, 2024</strong></span></p>
                                    <p><strong>Running a successful salon requires more than just great styling skills&mdash;it&rsquo;s about efficient management. Here are five expert tips to streamline operations and grow your salon business:</strong></p>
                                    <p style="padding-left: 40px;">📅 <strong data-start="327" data-end="362">Automate appointment scheduling</strong> to reduce no-shows<br data-start="381" data-end="384">🎁 <strong data-start="387" data-end="413">Offer loyalty programs</strong> to retain clients<br data-start="431" data-end="434">👩&zwj;💼 <strong data-start="440" data-end="467">Track staff performance</strong> and optimize schedules<br data-start="490" data-end="493">💬 <strong data-start="496" data-end="516">Engage customers</strong> with personalized experiences<br data-start="546" data-end="549">📊 <strong data-start="552" data-end="579">Use reports &amp; analytics</strong> to make data-driven decisions</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="post__body-reactions-acks"><strong data-start="613" data-end="652">Ready to scale your salon business?</strong> Frezka makes it easy!</div>
                </div>
            </div>
        </div>
    </div>',
                'total_view'  => 0,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => '2025-02-27 10:26:15',
                'updated_at'  => '2025-02-27 11:01:26',
            ],
            [
                'auther_id'   => 15,
                'title'       => 'How to Increase Salon Revenue with Packages',
                'status'      => 1,
                'description' => '<p><strong>Want steady cash flow for your salon? Offer memberships &amp; service packages!</strong></p>
<p style="padding-left: 40px;">🎟️ <strong data-start="1510" data-end="1530">VIP Memberships:</strong> Exclusive discounts &amp; priority booking<br data-start="1569" data-end="1572">📦 <strong data-start="1575" data-end="1595">Service Bundles:</strong> Prepaid packages for multiple visits<br data-start="1632" data-end="1635">💳 <strong data-start="1638" data-end="1669">Auto-Renewal Subscriptions:</strong> Monthly billing for loyal clients</p>
<p style="padding-left: 40px;">💡 <strong data-start="1710" data-end="1773">With Frezka, managing memberships &amp; packages is effortless!</strong></p>',
                'total_view'  => 0,
                'created_at'  => '2025-02-27 10:57:54',
                'updated_at'  => '2025-02-27 11:02:00',
                'created_by'  => 1,
                'updated_by'  => 1,
                'image' => 'blog-2.jpg',

            ],
            [
                'auther_id'   => 16,
                'title'       => 'Top 10 Marketing Strategies for Salons & Spas',
                'status'      => 1,
                'description' => '<p>Want more customers? Try these <strong data-start="1954" data-end="1981">10 marketing strategies</strong> for your salon:</p>
<p><strong data-start="2005" data-end="2048">1️⃣</strong> Build a website &amp; social media presence<br data-start="2048" data-end="2051">2️⃣ Run limited-time discounts &amp; flash sales<br data-start="2099" data-end="2102">3️⃣ Create referral &amp; loyalty programs<br data-start="2144" data-end="2147">4️⃣ Advertise on Google &amp; Facebook<br data-start="2185" data-end="2188">5️⃣ Partner with influencers &amp; local businesses<br data-start="2239" data-end="2242">6️⃣ Offer free consultations for new clients<br data-start="2290" data-end="2293">7️⃣ Send personalized SMS/email promotions<br data-start="2339" data-end="2342">8️⃣ Run seasonal &amp; holiday promotions<br data-start="2383" data-end="2386">9️⃣ List your salon on Google &amp; Yelp<br data-start="2426" data-end="2429">🔟 Use Frezka&rsquo;s marketing tools to automate campaigns!</p>',
                'total_view'  => 0,
                'created_at'  => '2025-02-27 10:58:54',
                'updated_at'  => '2025-02-27 10:58:54',
                'created_by'  => 1,
                'updated_by'  => 1,
                'image' => 'blog-3.jpg',

            ],
            [
                'auther_id'   => 17,
                'title'       => 'How to Use Customer Feedback to Improve Your Salon',
                'status'      => 1,
                'description' => '<p><strong>Customer feedback is your secret weapon for success! Here&rsquo;s how to use it effectively:</strong></p>
<p style="padding-left: 40px;" data-start="3205" data-end="3389">✅ Collect feedback via surveys &amp; reviews<br data-start="3249" data-end="3252">✅ Respond to complaints professionally<br data-start="3294" data-end="3297">✅ Use insights to enhance services<br data-start="3335" data-end="3338">✅ Reward loyal customers who provide feedback</p>
<p data-start="3391" data-end="3461">💡 <strong data-start="3394" data-end="3459">Frezka&rsquo;s CRM tools help you stay connected with your clients!</strong></p>',
                'total_view'  => 0,
                'created_at'  => '2025-02-27 11:00:26',
                'updated_at'  => '2025-02-27 11:00:26',
                'created_by'  => 1,
                'updated_by'  => 1,
                'image' => 'blog-4.jpeg',

            ],
            [
                'auther_id'   => 18,
                'title'       => 'How Online Payments Can Boost Your Salon’s Growth',
                'status'      => 1,
                'description' => '<p data-start="3606" data-end="3701">Cash-only businesses are losing customers! Here&rsquo;s why online payments are a <strong data-start="3682" data-end="3698">game-changer</strong>:</p>
<p style="padding-left: 40px;" data-start="3703" data-end="3909">💳 <strong data-start="3706" data-end="3754">Faster checkouts &amp; convenience for customers</strong><br data-start="3754" data-end="3757">📊 <strong data-start="3760" data-end="3801">Better financial tracking &amp; invoicing</strong><br data-start="3801" data-end="3804">🔒 <strong data-start="3807" data-end="3860">Secure transactions with multiple payment options</strong><br data-start="3860" data-end="3863">💡 <strong data-start="3866" data-end="3907">Boost sales with prepaid appointments</strong></p>
<p data-start="3911" data-end="3968">💡 <strong data-start="3914" data-end="3966">Frezka integrates with Stripe, PayPal, and more!</strong></p>',
                'total_view'  => 0,
                'created_at'  => '2025-02-27 11:03:05',
                'updated_at'  => '2025-02-27 11:03:05',
                'created_by'  => 1,
                'updated_by'  => 1,
                'image' => 'blog-5.jpeg',

            ],

            [
                'auther_id'   => 17,
                'title'       => 'The Future of AI & Automation in Salons',
                'status'      => 1,
                'description' => '<p data-start="4668" data-end="4743">AI is <strong data-start="4674" data-end="4713">revolutionizing the beauty industry</strong>! Here&rsquo;s what&rsquo;s coming next:</p>',
                'total_view'  => 0,
                'created_at'  => '2025-02-27 11:03:05',
                'updated_at'  => '2025-02-27 11:03:05',
                'created_by'  => 1,
                'updated_by'  => 1,
                'image' => 'blog-6.jpg',

            ],
            [
                'auther_id'   => 1,
                'title'       => 'Must-Have Features in a Modern Salon Booking System',
                'status'      => 1,
                'image'       => 'blog_img3919561751543988modern.avif',
                'description' => '<p><strong>Your salon deserves a smarter booking system. Here\'s what to look for:</strong></p>
        <p style="padding-left: 40px;">
        🗓️ <strong>Real-time appointment scheduling</strong> for staff & customers<br>
        🔔 <strong>Automated reminders</strong> to reduce no-shows<br>
        📱 <strong>Mobile-friendly design</strong> to book anytime, anywhere<br>
        💳 <strong>Integrated payment support</strong> for convenience<br>
        📊 <strong>Analytics & reporting</strong> to track performance<br>
        </p>
        <p><strong>With Frezka, enjoy all these features in one easy platform!</strong></p>',
                'total_view'  => 0,
                'created_at'  => '2025-02-27 11:05:00',
                'updated_at'  => '2025-02-27 11:05:00',
                'created_by'  => 2,
                'updated_by'  => 2,
            ],
            [
                'auther_id'   => 3,
                'title'       => 'Why Every Salon Needs a Digital Presence in 2025',
                'status'      => 1,
                'image'       => 'blog_img5577981751438641saloon.avif',
                'description' => '<p><strong>Having an online presence is no longer optional. Here\'s why it matters:</strong></p>
        <p style="padding-left: 40px;">
        🌐 <strong>Reach more customers</strong> through Google & social media<br>
        🧑‍💻 <strong>24/7 booking access</strong> via your website or app<br>
        ⭐ <strong>Showcase reviews</strong> to build trust & credibility<br>
        📸 <strong>Display your portfolio</strong> with a professional look<br>
        💬 <strong>Stay connected</strong> with automated messages & updates<br>
        </p>
        <p><strong>Frezka helps you build a powerful digital presence in minutes!</strong></p>',
                'total_view'  => 0,
                'created_at'  => '2025-02-27 11:05:00',
                'updated_at'  => '2025-02-27 11:05:00',
                'created_by'  => 2,
                'updated_by'  => 2,
            ],

            [
                'auther_id'   => 5,
                'title'       => 'How Multi-Branch Salons Can Simplify Operations with Frezka',
                'status'      => 1,
                'image'       => 'blog_img5636781751438546multibranch.avif',
                'description' => '<p><strong>Manage all your salon branches from one centralized system without the chaos of spreadsheets or manual records.</strong></p>
        <p style="padding-left: 40px;">
        🌐 <strong>Central Admin Control</strong> – Manage staff, settings, and services across all branches.<br>
        📍 <strong>Branch-specific Reporting</strong> – Track each location’s performance separately.<br>
        👥 <strong>Role-Based Access</strong> – Give branch managers the right permissions.<br>
        🔄 <strong>Service Syncing</strong> – Keep consistent offerings across locations.<br>
        📅 <strong>Cross-branch Booking Support</strong> – Let clients book from any nearby outlet<br>
        </p>
        <p><strong>Frezka brings structure to multi-location salon chains effortlessly.</strong></p>',
                'total_view'  => 0,
                'created_at'  => '2025-02-27 11:10:00',
                'updated_at'  => '2025-02-27 11:10:00',
                'created_by'  => 2,
                'updated_by'  => 2,
            ],
            [
                'auther_id'   => 7,
                'title'       => 'From Manual to Magical: Automating Your Salon with Frezka',
                'status'      => 1,
                'image'       => 'blog_img1237311751438508digital.avif',
                'description' => '<p><strong>If you\'re still juggling pen and paper, here’s how automation can save you hours and scale your business effortlessly.</strong></p>
        <p style="padding-left: 40px;">
        ⚙️ <strong>Automated Appointments & Reminders</strong><br>
        🧾 <strong>Instant Billing & Invoicing</strong><br>
        👤 <strong>CRM for Client Notes & History</strong><br>
        🔄 <strong>Recurring Package Management</strong><br>
        🧑‍🤝‍🧑 <strong>Staff Duty & Payroll Automation</strong><br>
        </p>
        <p><strong>Let Frezka do the heavy lifting while you focus on styling!</strong></p>',
                'total_view'  => 0,
                'created_at'  => '2025-02-27 11:10:00',
                'updated_at'  => '2025-02-27 11:10:00',
                'created_by'  => 2,
                'updated_by'  => 2,
            ],
            [
                'auther_id'   => 4,
                'title'       => 'More Than Just Bookings: The Hidden Power of Frezka\'s Dashboard & Reports',
                'status'      => 1,
                'image'       => 'blog_img2280721751438590dashboard.avif',
                'description' => '<p><strong>Your salon generates a wealth of data – are you using it to your advantage?</strong></p>
        <p><strong>Explore how Frezka\'s intuitive dashboards and comprehensive reports transform raw data into actionable insights:</strong></p>
        <p style="padding-left: 40px;">
        🚀 <strong>Spot Trends Early</strong> – Identify popular services, peak times, and client behavior patterns.<br>
        🏆 <strong>Evaluate Staff Performance</strong> – Understand individual and team strengths to foster growth.<br>
        💡 <strong>Optimize Marketing Spend</strong> – See which campaigns drive the most revenue.<br>
        🧠 <strong>Make Informed Decisions</strong> – Move beyond guesswork with data-backed strategies for pricing, staffing, and promotions.<br>
        </p>
        <p><strong>Frezka’s reports turn data into growth strategies.</strong></p>',
                'total_view'  => 0,
                'created_at'  => '2025-02-27 11:10:00',
                'updated_at'  => '2025-02-27 11:10:00',
                'created_by'  => 2,
                'updated_by'  => 2,
            ],
            [
                'auther_id'   => 5,
                'title'       => 'Why Reviews Matter: Build Salon Trust Online',
                'status'      => 1,
                'image'       => 'medium-shot-young-people-with-reviews.jpg',
                'description' => '<p><strong>Positive reviews attract more clients. Here’s how to leverage them:</strong></p>
        <p style="padding-left: 40px;">
        🌟 <strong>Ask happy customers to leave feedback</strong><br>
        📩 <strong>Follow up with review requests via email/SMS</strong><br>
        🗣️ <strong>Respond to all reviews—good or bad</strong><br>
        🖼️ <strong>Highlight top reviews on your homepage</strong><br>
        💡 <strong>Use Frezka to automate review collection</strong><br>
        </p>
        <p><strong>Build trust and grow your salon organically!</strong></p>',
                'total_view'  => 0,
                'created_at'  => '2025-02-27 11:05:00',
                'updated_at'  => '2025-02-27 11:05:00',
                'created_by'  => 2,
                'updated_by'  => 2,
            ],


        ];
        $dummyImages = [
            'blog-2.jpeg',
            'blog-3.jpeg',
            'blog-4.jpeg',
            'blog-5.jpeg',
            'blog-6.jpg',
            'blog_img3919561751543988modern.avif',
            'blog_img5577981751438641saloon.avif',
            'blog_img5636781751438546multibranch.avif',
            'blog_img1237311751438508digital.avif',
            'blog_img2280721751438590dashboard.avif',
            'medium-shot-young-people-with-reviews.jpg',
        ];
        // Set destination folder for blog images
        $destinationFolder = public_path('blog/images/');
        if (!File::exists($destinationFolder)) {
            File::makeDirectory($destinationFolder, 0777, true);
        }
        if (env('IS_DUMMY_DATA')) {
            foreach ($blogs as $blogData) {

                // Define the source path where your dummy images are stored
                $sourceImage = public_path('blog/images/' . $blogData['image']);
                if (File::exists($sourceImage)) {
                    // Generate a unique image name similar to your controller
                    $img_name = 'blog_img' . rand(100000, 999999) . time() . '.' . pathinfo($sourceImage, PATHINFO_EXTENSION);
                    $destinationPath = $destinationFolder . $img_name;
                    File::copy($sourceImage, $destinationPath);
                    // Store relative path in the blog data
                    $blogData['image'] = 'blog/images/' . $img_name;
                } else {
                    $blogData['image'] = null;
                }
                $blog = Blog::create($blogData);
                $blog->image = $blogData['image'] ?? null;
                $blog->save();
            }
        }
    }
}
