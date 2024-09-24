=== FlippingBook ===
Contributors: FlippingBook
Tags: flipbook, flip book, pdf flip book, pdf viewer, embed pdf, magazine, catalog, ebook, brochure, booklet, page flip, pdf
Requires at least: 3.2
Requires PHP: 5.5.0
Tested up to: 6.5.5
Stable tag: 2.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
FlippingBook Plugin is a flipbook PDF viewer solution. It allows you to embed flipbooks made via FlippingBook Online or FlippingBook Publisher into WordPress in one step, without any extra fuss. 

You don’t have to be make do with embedding PDFs anymore—turn them into responsive interactive HTML5 flipbooks instead and then use the plugin to embed them into a webpage in a click. Your flipbook magazine or brochure will become a seamless feature of your website, ready to engage.

== Installation ==
Here’s how to make the plugin work for your flipbooks.

1. Download and activate the plugin through the Plugins menu in the WordPress admin dashboard.
2. Copy the URL of your FlippingBook from the browser and paste it into the body of your post. For testing purposes, you can use our demo flipbook: [https://online.flippingbook.com/view/631977/](https://online.flippingbook.com/view/631977/). Simply embed it into your page and click on the **Preview** button to see how it looks.
3. To create your own publications with FlippingBook, you can use [one of our products](https://flippingbook.com/).

You can find more information on how to install and configure the plugin in our [Help Center article](https://flippingbook.com/help/publisher-2/sharing-and-embedding/installation-of-flippingbook-wordpress-plugin).

== Upgrade Notice ==

== About FlippingBook ==
FlippingBook helps create all kinds of digital documents for more than 50,000 customers around the world. If you’re new to the format, [check out our PDF flipbook examples](https://flippingbook.com/presentation-examples) and get inspired to create your own 3D flipbook.

https://www.youtube.com/watch?v=hpnRodjyiUk

== How to embed a flipbook into WordPress ==
If you’re a FlippingBook user:
1. Copy the link to the PDF flipbook you want to embed.
2. Go to the admin panel of your WordPress website. Find FlippingBook Plugin through the Plugin menu and activate it.
3. Now you can simply paste your flipbook sharing link into the body of your post, and the link will be converted into a neat oEmbed automatically.

== How to embed a PDF into WordPress ==
If you’ve never used FlippingBook before:
1. Sign up for FlippingBook and [create your first flipbook](https://www.youtube.com/watch?v=6NUAMSHQtCc)*—just upload your PDF and customize it. 
2. Copy the sharing link to your flipbook when it’s ready.
3. Go to the admin panel of your WordPress website. Find FlippingBook Plugin through the Plugin menu and activate it.
4. Now you can simply paste your flipbook sharing link into the body of your post, and the link will be converted into a neat oEmbed automatically.

(*) You can either sign up for our paid subscription straight away or go for the trial to try out all the features first. Don’t forget to [choose the plan](https://flippingbook.com/order-online) that works best for you once your trial is over—otherwise your flipbooks will go offline.

== Features ==

= Admin page =
You can tweak the settings of your oEmbed via the admin page of your WordPress website.

* **Make your flipbook responsive and customize the oEmbed ratio**
These two settings allow you to make the FlippingBook oEmbeds responsive.
Instead of having a default size, your embeds will fill the container by width and retain the ratio specified in the oEmbed ratio field.
The **Make oEmbed responsive** setting will apply to all new oEmbeds. You’ll need to clear the oEmbed cache if you’d like to apply the setting to your existing FlippingBook oEmbeds. 

* **Set a custom domain name**
If you are a FlippingBook Online user and you have your Custom Domain set up, you should enter the domain name in the relevant field to enable the oEmbed and the shortcode support for your custom domain. You can get more information about custom domains in [this article](https://flippingbook.com/help/online/other-features-and-options/branded-urls-in-flippingbook-online).

= Shortcode = 
With a **shortcode**, you can customize your WordPress flip book even further. 

To do so, choose **Embed** → **Embed into WordPress** in your FlippingBook Online account and **Publication** → **Get Embed Code** → **WordPress** in FlippingBook Publisher if you host on the cloud. Customize the look and the size of your oEmbed there and then just copy the embed code.

You can also make the **[flipbook]** shortcode by yourself to specify all the attributes you may need, such as:

* `width`—the width of your embed in pixels or percentages
* `height`—the height of your embed in pixels (you can set it to `auto`)
* `ratio`—your desired width:height ratio (e.g. `16:9`, `4:3`, etc) 
Note: If you set a specified ratio, you need to set the height to `auto` so that it could be adjusted according to your chosen ratio.
* `page`—the page you want your 3D flipbook to be opened at
* `mode`—you can set your PDF flipbook to be viewed as a `link` if you don’t want to have a full embed on your page 
Note: Best used together with the lightbox attribute (see below).
* `lightbox`—by default, we open embedded links or clickable covers in a lightbox, but if you set this parameter to `false`, the flipbook will be opened in a new tab instead
* `wheel`—set to `true`, if you want people to be able to flip through your embedded flipbook using the mouse wheel 
Note: This attribute may disrupt page scrolling when the cursor is over the embed.

= Shortcode examples =
This shortcode will display your PDF flip book in a 800x550px window:

`[flippingbook width="800px" height="550px"]https://online.flippingbook.com/view/631977/[/flippingbook]`

This shortcode will display your WordPress flipbook in full available width, while maintaining the 16:9 aspect ratio:

`[flippingbook ratio="16:9" width="100%" height="auto"]https://online.flippingbook.com/view/631977/[/flippingbook]`

This shortcode will display a flipbook cover, which will open in a lightbox when a user clicks to open it:

`[flippingbook width="200px" height="200px" lightbox="true"]https://online.flippingbook.com/view/631977/[/flippingbook]`

This shortcode will display a link with your flip book title, which will open in a lightbox when a user clicks to open it:

`[flippingbook mode="link" lightbox="true"]https://online.flippingbook.com/view/631977/[/flippingbook]`

== Screenshots ==
1. Paste the URL into the body of your blog post.
2. Or paste the [flippingbook] shortcode into the body of your blog post.
3. Publish the post and see your interactive flipbook embedded!
4. Set the plugin options in the WP Admin dashboard.

== Frequently Asked Questions ==
= What if I want to embed a digital brochure or a magazine into WordPress but I don’t have it in the PDF format? =
You’ll have to create a PDF first. Read our blog post [The Best PDF Creating Tools for Non-Designers](https://flippingbook.com/blog/how-to-create-pdfs)—it will help you find the right tool.

= Can I embed a PDF directly through your flip book WordPress plugin? =
No, you have to be a FlippingBook Online or FlippingBook Publisher user first. You’ll need to convert a PDF via one of our solutions to get a flipbook you can then embed via our 3D flipbook plugin.

= What WordPress plan do I need to use the FlippingBook plugin? =
Like with all plugins, our plugin is available starting from the Business plan.

= Does FlippingBook have a free plan? =
We don’t have a free plan but you can try FlippingBook for free, no credit card required. All features and templates are available to you during the trial, bar Custom Domain. You can check out all our plans and prices [here](https://flippingbook.com/order-online).

== Changelog ==
= 2.0.1 =
* Minor updates.
= 2.0 =
* We updated the plugin to support the latest Wordpress version (tested up to 5.8).
* We added Custom Domains support for FlippingBook Online.
* We added FlippingBook Cloud Bookshelves oEmbed support.
* We added a settings page to the Wordpress admin dashboard.
= 1.3 =
* Improved HTML5 publications support.
* Disabled the Flash plugin detection for HTML5 publications.
= 1.2.5 =
* Supports HTML5 publications.
= 1.2.0 =
* Supports FlippingBook Online publications.
* Uses external oEmbed provider for FlippingBook Online.
= 1.1.0 =
* Supports publications with friendly URLs.
