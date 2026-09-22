<?php
/**
 * About Us — copy and photos from the live Wix page
 * https://www.watphou-travels.com/about-us
 * (Excel About Us sheet: “Same as our old website”)
 */
defined( 'ABSPATH' ) || exit;

get_header();

$img = WATPHOU_THEME_URI . '/assets/images/about';

/**
 * @param string               $base  Theme about-images URL.
 * @param array<int, string[]> $items [ filename, alt ] pairs.
 * @param string               $group Lightbox group name (prev/next within this row).
 */
$gallery = static function ( string $base, array $items, string $group ): void {
	echo '<div class="wpt-about__gallery">';
	foreach ( $items as $item ) {
		$url = $base . '/' . $item[0];
		printf(
			'<figure><a class="wpt-lightbox-trigger" href="%1$s" data-wpt-lightbox data-wpt-group="%3$s"><img src="%1$s" alt="%2$s" loading="lazy" decoding="async"></a></figure>',
			esc_url( $url ),
			esc_attr( $item[1] ),
			esc_attr( $group )
		);
	}
	echo '</div>';
};
?>
<main class="wpt-main">
	<article class="wpt-about">
		<div class="wpt-container">
			<?php
			if ( function_exists( 'watphou_core_breadcrumbs' ) ) {
				watphou_core_breadcrumbs();
			}
			?>

			<section class="wpt-about__section" aria-labelledby="wpt-about-heading">
				<h1 id="wpt-about-heading"><?php esc_html_e( 'About Watphou Travels', 'watphou-travels' ); ?></h1>
				<p><?php esc_html_e( 'Established in 2008, Watphou Travels is a specialized travel agency focused on delivering exceptional tours throughout Southern Laos. We offer a wide variety of options, from day trips to multi-day and extended adventures lasting up to seven days, tailored to suit every traveler’s preferences. Conveniently located right in front of Pakse Hotel & Restaurant in Pakse, Champasak Province, our office serves as a convenient starting point for your journey. We pride ourselves on crafting private, customized tours that provide an authentic and immersive experience of the region. Every tour includes a private car and professional driver, with the option to add a qualified guide for deeper cultural insights.', 'watphou-travels' ); ?></p>
				<p><?php esc_html_e( 'Our multilingual team speaks English, Italian, French, Thai, and Lao, combining local knowledge with international professionalism. We are dedicated to ensuring a seamless, memorable, and enriching travel experience for all our guests. With Watphou Travels, exploring Southern Laos becomes effortless and unforgettable.', 'watphou-travels' ); ?></p>
				<?php
				$gallery(
					$img,
					array(
						array( 'office-1.jpg', 'Watphou Travels office' ),
						array( 'office-2.jpg', 'Watphou Travels office' ),
						array( 'office-3.jpg', 'Watphou Travels office' ),
					),
					'office'
				);
				?>
			</section>

			<section class="wpt-about__section" aria-labelledby="wpt-about-hospitality">
				<h2 id="wpt-about-hospitality"><?php esc_html_e( 'Our Hospitality Services', 'watphou-travels' ); ?></h2>
				<p><?php esc_html_e( 'As part of our commitment to exceptional service, we proudly operate Pakse Hotel & Restaurant along with two unique dining experiences: Le Panorama Rooftop and Pakse Burger 66. These offerings complement our travel services by providing comfortable accommodation and memorable culinary moments.', 'watphou-travels' ); ?></p>

				<div class="wpt-about__venue">
					<h3><?php esc_html_e( 'Pakse Hotel & Restaurant', 'watphou-travels' ); ?></h3>
					<p><?php esc_html_e( 'Pakse Hotel offers comfortable and welcoming accommodation in the heart of Pakse, ideal for travelers exploring Southern Laos. The hotel features well-appointed rooms with modern amenities designed for a restful stay.', 'watphou-travels' ); ?></p>
					<p><?php esc_html_e( 'Guests enjoy convenient access to major attractions and the nearby Mekong River. Each morning, a generous breakfast buffet is served, featuring a wide selection of fresh local and international dishes to energize you for the day ahead. The on-site restaurant also serves a variety of flavorful meals throughout the day, prepared with fresh ingredients. Friendly staff and attentive service ensure every guest feels at home.', 'watphou-travels' ); ?></p>
					<?php
					$gallery(
						$img,
						array(
							array( 'hotel-1.jpg', 'Pakse Hotel & Restaurant' ),
							array( 'hotel-2.jpg', 'Pakse Hotel & Restaurant' ),
							array( 'hotel-3.jpg', 'Pakse Hotel & Restaurant' ),
						),
						'hotel'
					);
					?>
					<p class="wpt-about__meta">
						<?php esc_html_e( 'Address: No. 5 Ban Wat Luang, Pakse, Champasak Province - 16000, Southern Laos', 'watphou-travels' ); ?><br>
						<?php esc_html_e( 'Phone/WhatsApp:', 'watphou-travels' ); ?>
						<a href="https://wa.me/8562056030192">+85620 5603 0192</a>,
						<?php esc_html_e( 'Facebook page:', 'watphou-travels' ); ?>
						<a href="https://www.facebook.com/hotel.pakse/" rel="noopener noreferrer">https://www.facebook.com/hotel.pakse/</a>
					</p>
				</div>

				<div class="wpt-about__venue">
					<h3><?php esc_html_e( 'Le Panorama Rooftop Restaurant', 'watphou-travels' ); ?></h3>
					<p><?php esc_html_e( 'Le Panorama Rooftop provides an unforgettable dining experience with stunning views over Pakse and the Mekong River. This elegant rooftop venue is perfect for relaxing evenings and special occasions. The menu features a mix of local favorites and international cuisine, crafted by skilled chefs. Guests can enjoy refreshing cocktails while taking in breathtaking sunsets.', 'watphou-travels' ); ?></p>
					<p><?php esc_html_e( 'From 5 to 6 pm, enjoy Happy Hour with a 20% discount on selected drinks. The stylish atmosphere combines comfort with a vibrant social vibe, making it an ideal spot to unwind after a day of exploring.', 'watphou-travels' ); ?></p>
					<?php
					$gallery(
						$img,
						array(
							array( 'panorama-1.jpg', 'Le Panorama rooftop in Pakse' ),
							array( 'panorama-2.jpg', 'Le Panorama rooftop in Pakse' ),
							array( 'panorama-beer.jpg', 'Imported beer in Pakse' ),
						),
						'panorama'
					);
					?>
					<p class="wpt-about__meta">
						<?php esc_html_e( 'Address: Rooftop of Pakse Hotel & Restaurant, No. 5 Ban Wat Luang, Pakse, Champasak Province - 16000, Southern Laos', 'watphou-travels' ); ?><br>
						<?php esc_html_e( 'Hours: Opens 4.30 PM, Closes 11.00 PM', 'watphou-travels' ); ?><br>
						<?php esc_html_e( 'Phone/WhatsApp:', 'watphou-travels' ); ?>
						<a href="https://wa.me/8562055621261">+85620 5562 1261</a>,
						<?php esc_html_e( 'Facebook page:', 'watphou-travels' ); ?>
						<a href="https://www.facebook.com/Lepanoramarooftoprestaurant" rel="noopener noreferrer">https://www.facebook.com/Lepanoramarooftoprestaurant</a>
					</p>
				</div>

				<div class="wpt-about__venue">
					<h3><?php esc_html_e( 'Pakse Burger 66 Restaurant', 'watphou-travels' ); ?></h3>
					<p><?php esc_html_e( 'Pakse Burger 66 is a popular casual eatery known for its delicious homemade burgers made from quality ingredients. Open for lunch, it offers a laid-back setting where locals and travelers alike can unwind and enjoy hearty meals. Beyond burgers, the menu includes a variety of sides and beverages to satisfy all tastes.', 'watphou-travels' ); ?></p>
					<p><?php esc_html_e( 'The restaurant’s commitment to great food and warm service has made it a favorite spot in Pakse. Whether for a quick bite or a relaxed meal, Pakse Burger 66 delivers satisfying flavors in a welcoming atmosphere.', 'watphou-travels' ); ?></p>
					<?php
					$gallery(
						$img,
						array(
							array( 'burger-1.jpg', 'Pakse Burger 66' ),
							array( 'burger-2.jpg', 'Pakse Burger 66' ),
							array( 'burger-3.jpg', 'Pakse Burger 66' ),
						),
						'burger'
					);
					?>
					<p class="wpt-about__meta">
						<?php esc_html_e( 'Address: Ground floor of Pakse Hotel & Restaurant, No. 5 Ban Wat Luang, Pakse, Champasak Province - 16000, Southern Laos', 'watphou-travels' ); ?><br>
						<?php esc_html_e( 'Hours: Opens 10:00 AM, Closes 14.00 PM', 'watphou-travels' ); ?><br>
						<?php esc_html_e( 'Phone:', 'watphou-travels' ); ?>
						<a href="tel:+85631212131">031 212 131</a>,
						<?php esc_html_e( 'Facebook page:', 'watphou-travels' ); ?>
						<a href="https://www.facebook.com/Pakseburger" rel="noopener noreferrer">https://www.facebook.com/Pakseburger</a>
					</p>
				</div>
			</section>

			<section class="wpt-about__section" aria-labelledby="wpt-about-partners">
				<h2 id="wpt-about-partners"><?php esc_html_e( 'Our Partnerships for Community Development in Southern Laos', 'watphou-travels' ); ?></h2>
				<p><?php esc_html_e( 'Local people are our main partners, and our mission is to support communities in a way that is socially, economically, and environmentally sustainable. All of our tours follow responsible eco-tourism principles: traveling to natural areas while improving the wellbeing of local people, minimizing environmental impact, and helping protect delicate cultures and habitats.', 'watphou-travels' ); ?></p>

				<div class="wpt-about__venue">
					<h3><?php esc_html_e( 'University of Calgary – Cumming School of Medicine', 'watphou-travels' ); ?></h3>
					<p><?php esc_html_e( 'We are proud to work alongside the University of Calgary through its Cumming School of Medicine as part of the Laos Community Development Initiative. The University’s long-standing commitment to rural Laos—especially in medical education and community support—greatly strengthens our efforts on the ground.', 'watphou-travels' ); ?></p>
					<p><?php esc_html_e( 'At the heart of this initiative is Dr. Janice Heard, a clinical professor whose compassion, expertise, and leadership have been essential in launching and sustaining major projects, including the Meuang Kang School Project in Champasak Province. Her work connects academic excellence with real community needs, and her dedication has been recognized through the Order of the University of Calgary for international development leadership.', 'watphou-travels' ); ?></p>
					<p><?php esc_html_e( 'Through this collaboration, we are able to:', 'watphou-travels' ); ?></p>
					<ul>
						<li><?php esc_html_e( 'Rebuild and improve schools in Meuang Kang and nearby villages.', 'watphou-travels' ); ?></li>
						<li><?php esc_html_e( 'Strengthen community engagement through regular planning and coordination meetings.', 'watphou-travels' ); ?></li>
						<li><?php esc_html_e( 'Support the University’s medical education mission by welcoming medical students and residents to teach and learn in rural Laos.', 'watphou-travels' ); ?></li>
						<li><?php esc_html_e( 'Maintain long-term project sustainability through ongoing fundraising and donor outreach.', 'watphou-travels' ); ?></li>
					</ul>
					<p><?php esc_html_e( 'This partnership helps us move beyond infrastructure—towards empowering communities, nurturing future generations, and creating a lasting, positive impact across Southern Laos.', 'watphou-travels' ); ?></p>
					<p>
						<?php esc_html_e( 'For more details about these meaningful initiatives, please visit:', 'watphou-travels' ); ?><br>
						<a href="https://engage.ucalgary.ca/LaosCommunityDevelopment" rel="noopener noreferrer">https://engage.ucalgary.ca/LaosCommunityDevelopment</a>
					</p>
					<?php
					$gallery(
						$img,
						array(
							array( 'school-1.jpg', 'School in Champasak' ),
							array( 'calgary-1.jpg', 'University of Calgary in Champasak' ),
							array( 'calgary-2.jpg', 'University of Calgary in Champasak' ),
						),
						'school'
					);
					?>
				</div>

				<div class="wpt-about__venue">
					<h3><?php esc_html_e( 'Les Colchés d’Asie Association', 'watphou-travels' ); ?></h3>
					<p><?php esc_html_e( 'Our work is also strengthened by the meaningful support of Les Colchés d’Asie, a French association deeply dedicated to education and solidarity across Southeast Asia.', 'watphou-travels' ); ?></p>
					<p><?php esc_html_e( 'Two individuals, Mr. Gilbert Leseigneur and Mr. Jean-Claude Collet, have played an especially important role in bringing hands-on assistance, resources, and encouragement to our communities. Their long-term engagement, generosity, and personal involvement have helped improve school conditions, provide essential materials, and support vulnerable families in remote villages.', 'watphou-travels' ); ?></p>
					<p><?php esc_html_e( 'Through their efforts and the mission of Les Colchés d’Asie, we are able to:', 'watphou-travels' ); ?></p>
					<ul>
						<li><?php esc_html_e( 'Deliver direct support to rural schools and teachers.', 'watphou-travels' ); ?></li>
						<li><?php esc_html_e( 'Provide materials and improvements that enhance daily learning conditions.', 'watphou-travels' ); ?></li>
						<li><?php esc_html_e( 'Strengthen community activities focused on children’s education and well-being.', 'watphou-travels' ); ?></li>
						<li><?php esc_html_e( 'Build a reliable network of solidarity between France and Southern Laos.', 'watphou-travels' ); ?></li>
					</ul>
					<p><?php esc_html_e( 'Their commitment reflects the best of international friendship—quiet, generous, and deeply human.', 'watphou-travels' ); ?></p>
					<p>
						<?php esc_html_e( 'For full details about LESCOLCHE-ASIE (France):', 'watphou-travels' ); ?><br>
						<a href="https://www.lescolche-asie.fr/archives/2016" rel="noopener noreferrer">https://www.lescolche-asie.fr/archives/2016</a>
					</p>
					<?php
					$gallery(
						$img,
						array(
							array( 'students.jpg', 'Students in Champasak' ),
							array( 'colches.jpg', 'Les Colchés d’Asie Association in Champasak' ),
							array( 'school-2.jpg', 'School in Champasak' ),
						),
						'colches'
					);
					?>
				</div>

				<p><?php esc_html_e( 'Across all our partnerships, our mission is clear: to nurture future generations, strengthen local capacity, and create a positive, lasting impact for communities throughout Southern Laos.', 'watphou-travels' ); ?></p>
			</section>

			<section class="wpt-about__close" aria-labelledby="wpt-about-close">
				<h2 id="wpt-about-close"><?php esc_html_e( 'Watphou Travels', 'watphou-travels' ); ?></h2>
				<p><?php esc_html_e( 'Your Local Expert in Southern Laos', 'watphou-travels' ); ?></p>
				<p>
					<?php esc_html_e( 'Phone/WhatsApp:', 'watphou-travels' ); ?>
					<a href="<?php echo esc_url( function_exists( 'watphou_get_whatsapp_url' ) ? watphou_get_whatsapp_url() : 'https://wa.me/8562099495858' ); ?>">+85620 9949 5858</a><br>
					<?php esc_html_e( 'Email:', 'watphou-travels' ); ?>
					<a href="mailto:sales.watphoutravel@gmail.com">sales.watphoutravel@gmail.com</a>
					<?php esc_html_e( 'or', 'watphou-travels' ); ?>
					<a href="mailto:watphoutravel.of@gmail.com">watphoutravel.of@gmail.com</a><br>
					<?php esc_html_e( 'Address: Street N°5, Ban Vat Luang, Pakse - Laos', 'watphou-travels' ); ?>
				</p>
				<p><?php esc_html_e( 'If you have any questions, Let us help you!', 'watphou-travels' ); ?></p>
			</section>
		</div>
	</article>
</main>
<?php
get_footer();
