<?php
/**
 * Page copy recovered from the Wayback Machine (edgeexpo.co.za, 2016–2025 captures).
 *
 * Service pages share one layout (title bar, intro, three feature boxes, portfolio strip,
 * contact band). The copy lives here; edgeexpo_service_page() in inc/sections.php builds the
 * blocks, and the matching pattern files call it.
 *
 * @package Edge_Expos
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Contact band copy used at the foot of most pages (2025 home page wording).
 */
function edgeexpo_cta_copy(): array
{
    return array(
        'title' => 'Get in touch with us for a cutting edge experience',
        'text'  => 'Edge completely respects our client’s budgets. With years of experience within the Exhibition industry, we understand how much things cost, and as a result are able to not only design a stand-out-stand, but also make certain it is within the perimeters of our client’s budgets. Our goal is to make certain you get the best possible Return on Investment (ROI) and Brand Awareness opportunity available from this Experiential Marketing.',
    );
}

/**
 * Copy for each page that uses the service layout.
 *
 * Keys: title (title bar), crumb (parent crumb or ''), heading, leads (paragraphs), subhead,
 * boxes (icon, title, text), cta (title, text) or null for the default contact band,
 * button (label, url) for an extra button under the boxes.
 *
 * @param string $key Page key.
 */
function edgeexpo_page_content(string $key): array
{
    $content = array(
        'home'      => array(
            'title'   => '',
            'heading' => 'Exhibition Stands',
            'leads'   => array('The exhibition industry is increasingly being credited as a cutting edge marketing tool. It is the only form of below the line marketing that places a company face to face with their exact target market. Edge is an exhibition design and manufacturing company on the cutting edge of exhibition stand design and stand building.'),
            'subhead' => 'Edge Expos have 3 main principles that set us aside from the rest of the market:',
            'boxes'   => array(
                array('cogs', 'Excellent Service &amp; Project Management', 'Firstly we believe in Excellent Service and Project Management, right from the beginning until the very end. Whether the stand is a triple storey (cause we can do that) fully customized design, or a teeny tiny Portable modular exhibition stand, we will endeavor to give you the same stand out service, every time, all the time.'),
                array('heart', 'Passionate About Design', 'Secondly we believe in the power of design – we are PASSIONATE about design, and we believe that this is communicated in the innovative stand designs that we present to our clients. We want to stretch the limits of your imagination, we want your eyes to light up when you turn the first page of the presentation, and we work hard at making certain we are always on the cutting edge of new technologies and trends within the Exhibition industry so that we can achieve this.'),
                array('cube', 'Quality End Product', 'Lastly, but undoubtedly just as important as the first 2 principles, is the quality of the end product. Our Exhibition Stand Designs are beautiful, and we pride ourselves in making certain that the end Display Stand is just as beautiful if not better. We focus on finishes. We focus on on-time handovers. We focus on client satisfaction.'),
            ),
        ),
        'custom'    => array(
            'title'   => 'Custom',
            'crumb'   => 'Exhibition Stands',
            'heading' => 'Custom Exhibition Stands',
            'leads'   => array('You only get one chance to make a first impression. That is why having an eye catching Exhibition or Mall Activation Stand design is so important. Making certain that your Display stand communicates the correct corporate image to your client is critical to your return on investment (ROI). So how do you do this?'),
            'boxes'   => array(
                array('eye', 'Comprehensive Briefing and Brainstorming', 'Firstly you start with a detailed and accurate brief. The Sales team at Edge Expos have years of experience within the exhibition industry, and as a result have the tools to make certain that your brief is as comprehensive as possible. We don’t just sit back, scribble down notes and then make the designers do all the hard work. We will brainstorm with you, we will make certain we ask the right questions at the right time and get all the information needed to deliver on the design!'),
                array('star', 'Genius Designers', 'The next step is where the true talent comes about; we like to think of our design department as design geniuses! They love what they do, and they are extremely good at it. Even when a brief is point-for-point what the client wants, our designers will figure out a way to make certain that the design still exceeds the client’s expectations. In the scenario where the brief is open ended and our designers get to play a little – hold onto your hats! Their design will blow you away!'),
                array('cogs', 'Realistic Costs', 'The last step in a fantastic Exhibition Stand design is making certain the costs are realistic. We design to budget, and we build where the show is. So with workshops in Johannesburg, Cape Town and Durban, there are no major logistics or travel costs on our Display stand budgets, all of your Marketing budget goes into your design!'),
            ),
        ),
        'modular'   => array(
            'title'   => 'Modular',
            'crumb'   => 'Exhibition Stands',
            'heading' => 'Modular Exhibition Stands',
            'leads'   => array(
                'Hands down the best solution for a national roadshow – The strength of the product lies in its versatility. A modular Exhibition Stand is a pole and rail system which is purchased to own, can be built in nearly any configuration to nearly any size, and due to the aluminum structure, is light weight, durable, packs away space efficiently and is affordable to transport.',
                'Edge Expos are proud to be a national re-seller for a truly Innovative Proudly South African Product – The Modular Exhibition Stand.',
            ),
            'boxes'   => array(
                array('star', 'Fabric Tension Graphics', 'The fabric tension graphic is a seamless (height dependent) and crease aversive solution, which is dry-cleanable giving you years of use from one print, making this the ideal product for mall activations and Roadshows.'),
                array('bolt', 'Tool Free Solution', 'This Modular Exhibition system is a tool free solution, meaning that the end user can build the stand quickly and easily. Due to the fact that the product is sold out-right and not on a hire basis as is typical with most exhibition stands, it is a brilliant long-term cost saving solution.'),
                array('eye', 'Maximum Branding Opportunity', 'Last but not least, the Modular Exhibition Stand is beautiful. A seamless fabric print gives the stand the illusion of a custom exhibition stand, with the additional value of maximizing your branding opportunities.'),
            ),
            'button'  => array('Request our modular catalogue', home_url('/contact-us/')),
            'cta'     => array('Discover endless possibilities', 'The Modular Exhibition Stand offers you endless configurations to maximise your brand potential. Contact us today to find out more about this versatile solution.'),
        ),
        'system'    => array(
            'title'   => 'System',
            'crumb'   => 'Exhibition Stands',
            'heading' => 'System Exhibition Stands',
            'leads'   => array('System stands are the most cost effective and time effective solution with stand building technology on the market. For short build up’s and tight budgets, a system stand will communicate your companies desired message in the most effective way. The modularity of the system allows the Display stands to be built to any height or dimension that your Experiential Marketing Campaign desires.'),
            'boxes'   => array(
                array('eye', 'Maximise your Brand Awareness', 'System Exhibition Stands are designed in such a way that it maximizes the opportunities for Brand Awareness, allowing your company image and logo to be visible from all angles of the Display Stand.'),
                array('star', 'Innovative and Eye Catching', 'Don’t let all this functionality fool you – System stands, when designed by Edge Expos, are innovative and eye catching. Our expertise within the Exhibition industry, and knowledge of the product range allow our designers to push the boundaries of the systems creative expectations.'),
                array('cogs', 'Solution that Delivers', 'System Exhibition Stands are a no fuss, no stress expo stand solution. With Workshops in Johannesburg, Cape Town and Durban, System stands are the hassle free roadshow; mall activation and exhibition stand solution that will deliver on all of your exhibition needs.'),
            ),
            'cta'     => array('Let the system work for you', 'Find out how our system stands can meet your expectations.'),
        ),
        'pos'       => array(
            'title'   => 'Point of Sale',
            'heading' => 'Point of Sale',
            'leads'   => array('Edge Expos LOVE Custom designed Point of Sale. We thrive on the opportunity to deliver this effective Below the Line marketing tool to Retail Outlets and corporate offices nationwide. Our strength in this Point of Sale design lies in our innovative problem solving approach to the brief. There is no brief too challenging for us. There are many that we have never heard of before, and never expect to hear of again, but we relish the opportunity to solve your problems. We love the challenge that Point of Sale gives us, and we love the Client satisfaction we receive when we deliver what once seemed like the impossible.'),
            'boxes'   => array(
                array('star', 'Timeless Stands', 'Our solutions are not short-term installations, but rather custom designed Display Stands that are meant to increase your brand awareness and message for years to come.'),
                array('screen', 'Innovative Technology', 'Our work within Custom Point of Sale design has taken the latest Audio Visual, touch screen, tablet and Augmented Reality to interesting new levels and we are proficient in the content development and networking skills needed to deliver on this kind of innovative technology.'),
                array('truck', 'Range of Applications', 'Taking this approach into account, we have been involved in several Container Conversions, and Trailer transformations, which have provided long-term experiential marketing solutions for our clients.'),
            ),
        ),
        'shop'      => array(
            'title'   => 'Shop Fitting &amp; Corporate Interior',
            'heading' => 'Shop Fitting &amp; Corporate Interiors',
            'leads'   => array(
                'Shopfitting and corporate Interior design are not simply a matter of designing a beautiful shelf or reception counter and fitting them within a space. Corporate Identity, Consumer trends and shopping psychology have to be taken into account when designing a retail space, reception area or Stadium suite that will be eye catching and entice the end user to come into and feel welcomed by the space. Further to that, once inside the space, the experience has to be desirable and create an ambiance that encourages the consumer to stay within the store for longer, browse the shelves more comprehensively.',
                'This is where Edge comes in, our design team is all Interior Design Graduates who understand the importance of creating a flow within the space. We understand the value of wall space displays and showcases, when placed in the correct position and lit the right way, can make all the difference to your return on investment (ROI) and brand Awareness. Our team will plan, design, manufacture, install and project manage your Retail Design, reception area of Stadium Suite from Concept to Completion.',
            ),
            'boxes'   => array(),
        ),
        'container' => array(
            'title'   => 'Container Conversions',
            'heading' => 'Containers Conversions &amp; Trailer Transformations',
            'leads'   => array(
                'Custom designed Container conversions and trailer transformations have taken the Exhibition and Roadshow market by storm. Changing the face of trade shows, a fully customized container can be set up relatively quickly, in several different configurations. Due to the structural integrity of a container, it can be stacked to create double and triple storey stands. There is no limit to the environments that can be created by a container, and our design team love to push the boundaries with this concept. With options to hire a pre-existing solution, or have us custom design and manufacture your container, the Return on Investment (ROI) and brand awareness that the size of the containers give an exhibition stand is exceptional.',
                'Trailer Transformations have Revolutionized Road Shows and Events.',
            ),
            'boxes'   => array(
                array('truck', 'Easy Setup and Transport', 'Trailers are easy to set up, create fantastic brand awareness opportunities and, of course, are easy to transport.'),
                array('cogs', 'Cost Effective', 'These facts make taking a trailer on a roadshow or to an event a cost effective and headache free solution.'),
                array('star', 'The Sky is the Limit', 'Trailer sizes vary, so you can have a complete stage setup in your trailer, or a small coffee station.'),
            ),
        ),
    );

    return $content[$key] + array(
        'crumb'   => '',
        'subhead' => '',
        'boxes'   => array(),
        'button'  => null,
        'cta'     => null,
    );
}
