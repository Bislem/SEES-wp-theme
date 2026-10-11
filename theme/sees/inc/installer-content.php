<?php
/**
 * Default bilingual content used by the automatic setup (inc/installer.php).
 *
 * Texts come from the approved design. Facts the client hasn't supplied yet stay as
 * [PLACEHOLDERS]. Editing this file does not change existing pages — the installer only
 * creates what is missing.
 *
 * @package sees
 */

/**
 * @return array{pages:array,solutions:array,faqs:array,categories:array,projects:array,forms:array,strings:array}
 */
function sees_installer_content(): array {
	return array(

		/* ---------------- Pages: [ template, slug/title per language, fields per language ] ---------------- */
		'pages' => array(
			'home' => array(
				'template' => '',
				'order'    => 0,
				'en'       => array(
					'title'  => 'Home',
					'slug'   => 'home',
					'fields' => array(
						'hero_eyebrow'      => 'Engineering a smarter tomorrow',
						'hero_title'        => 'Smart Electrical Engineering Solutions',
						'hero_text'         => 'Delivering intelligent, reliable and sustainable electrical solutions for a brighter future.',
						'feature_1'         => 'Innovative Solutions',
						'feature_2'         => 'Sustainable Growth',
						'feature_3'         => 'Reliable Execution',
						'feature_4'         => 'People Focused',
						'about_eyebrow'     => 'About SEES',
						'about_title'       => 'Engineering Intelligence for Real-World Impact',
						'about_text'        => 'SEES delivers integrated electrical engineering solutions that combine innovation, quality and sustainability. We power progress across diverse sectors with a focus on reliability, efficiency and long-term value.',
						'story_title'       => 'Our Story',
						'story_text'        => 'Building smarter infrastructure for a better tomorrow.',
						'stat_1_value'      => '[10+]',
						'stat_1_label'      => 'Years of Experience',
						'stat_2_value'      => '[100+]',
						'stat_2_label'      => 'Projects Delivered',
						'stat_3_label'      => 'Trusted by Leading Partners',
						'stat_4_label'      => 'Commitment to Sustainable Solutions',
						'solutions_eyebrow' => 'Our Solutions',
						'solutions_title'   => 'Integrated electrical solutions, from design to operation.',
						'marquee_items'     => "Power Distribution\nSolar PV\nSubstations\nSmart Buildings\nنهندس بذكاء ونُشغّل بشكل أفضل\nOperations & Maintenance",
						'featured_eyebrow'  => 'Featured Project',
						'cta_kicker'        => 'نهندس بذكاء ونُشغّل بشكل أفضل',
						'cta_title'         => 'Let\'s power your next project.',
						'cta_text'          => 'Tell us what you\'re building — an engineer will get back to you with next steps.',
					),
				),
				'ar'       => array(
					'title'  => 'الرئيسية',
					'slug'   => 'الرئيسية',
					'fields' => array(
						'hero_eyebrow'      => 'نهندس غدًا أذكى',
						'hero_title'        => 'الحلول الهندسية الكهربائية الذكية',
						'hero_text'         => 'نقدّم حلولًا كهربائية ذكية وموثوقة ومستدامة من أجل مستقبل أكثر إشراقًا.',
						'feature_1'         => 'حلول مبتكرة',
						'feature_2'         => 'نمو مستدام',
						'feature_3'         => 'تنفيذ موثوق',
						'feature_4'         => 'الإنسان أولًا',
						'about_eyebrow'     => 'عن سيز',
						'about_title'       => 'ذكاء هندسي بأثر ملموس',
						'about_text'        => 'تقدّم سيز حلولًا هندسية كهربائية متكاملة تجمع بين الابتكار والجودة والاستدامة. نُسهم في دفع عجلة التقدّم في مختلف القطاعات مع تركيز على الموثوقية والكفاءة والقيمة على المدى الطويل.',
						'story_title'       => 'قصتنا',
						'story_text'        => 'نبني بنية تحتية أذكى من أجل غدٍ أفضل.',
						'stat_1_value'      => '[+10]',
						'stat_1_label'      => 'سنوات من الخبرة',
						'stat_2_value'      => '[+100]',
						'stat_2_label'      => 'مشروع منجز',
						'stat_3_label'      => 'شريك موثوق لكبرى الجهات',
						'stat_4_label'      => 'التزام بالحلول المستدامة',
						'solutions_eyebrow' => 'حلولنا',
						'solutions_title'   => 'حلول كهربائية متكاملة، من التصميم إلى التشغيل.',
						'marquee_items'     => "توزيع الطاقة\nالطاقة الشمسية\nالمحطات الفرعية\nالمباني الذكية\nEngineering Smarter, Powering Better\nالتشغيل والصيانة",
						'featured_eyebrow'  => 'مشروع مميّز',
						'cta_kicker'        => 'Engineering Smarter, Powering Better',
						'cta_title'         => 'لنُشغّل مشروعك القادم.',
						'cta_text'          => 'أخبرنا بما تبنيه، وسيتواصل معك أحد مهندسينا بالخطوات التالية.',
					),
				),
			),

			'about' => array(
				'template' => 'page-about.php',
				'order'    => 1,
				'en'       => array(
					'title'  => 'About',
					'slug'   => 'about',
					'fields' => array(
						'header_subtitle' => 'An electrical engineering company built on precision, safety and smart technology.',
						'header_arabic'   => 'سيز للحلول الهندسية الكهربائية الذكية',
						'story_eyebrow'   => 'Who we are',
						'story_title'     => 'Smart engineering for the way the region builds today.',
						'story_text'      => '<p>SEES — Smart Electrical Engineering Solutions — is based in Al Shahamah District, Makkah. We design, install and maintain electrical systems for commercial, residential and infrastructure projects, bringing careful engineering and modern technology to every installation.</p><p>[Add a short paragraph on the company\'s founding, leadership and key certifications.]</p>',
						'mission'         => 'To deliver integrated electrical solutions that are safe, efficient and built to last.',
						'vision'          => 'To be the region\'s most trusted partner for smart electrical engineering.',
						'values_eyebrow'  => 'Our values',
						'values_title'    => 'What guides every project we take on.',
						'value_1_title'   => 'Innovation',
						'value_1_text'    => 'Modern technology applied where it makes systems safer and smarter.',
						'value_2_title'   => 'Sustainability',
						'value_2_text'    => 'Efficient designs and renewable energy that lower long-term impact.',
						'value_3_title'   => 'Reliability',
						'value_3_text'    => 'Work done to code, tested, documented and delivered on schedule.',
						'value_4_title'   => 'People',
						'value_4_text'    => 'Certified teams who listen, communicate clearly and put safety first.',
						'tagline_1'       => 'نصمّم الراحة بهندسة ذكية',
						'tagline_2'       => 'Engineering Comfort, Perfected.',
						'cta_title'       => 'Ready to work with SEES?',
						'cta_text'        => 'Let\'s talk about your next electrical project.',
					),
				),
				'ar'       => array(
					'title'  => 'من نحن',
					'slug'   => 'من-نحن',
					'fields' => array(
						'header_subtitle' => 'شركة هندسة كهربائية قائمة على الدقة والسلامة والتقنية الذكية.',
						'header_arabic'   => 'Smart Electrical Engineering Solutions',
						'story_eyebrow'   => 'من نحن',
						'story_title'     => 'هندسة ذكية تواكب طريقة البناء في المنطقة اليوم.',
						'story_text'      => '<p>سيز للحلول الهندسية الكهربائية الذكية شركة مقرّها حي الشهامة في مكة المكرمة. نصمّم الأنظمة الكهربائية ونركّبها ونصونها للمشاريع التجارية والسكنية ومشاريع البنية التحتية، ونجمع في كل تركيب بين الهندسة الدقيقة والتقنيات الحديثة.</p><p>[أضف فقرة قصيرة عن تأسيس الشركة وقيادتها وأبرز شهاداتها.]</p>',
						'mission'         => 'تقديم حلول كهربائية متكاملة آمنة وفعّالة ومصمّمة لتدوم.',
						'vision'          => 'أن نكون الشريك الأكثر ثقة في المنطقة في مجال الهندسة الكهربائية الذكية.',
						'values_eyebrow'  => 'قيمنا',
						'values_title'    => 'ما يوجّه كل مشروع نتولّاه.',
						'value_1_title'   => 'الابتكار',
						'value_1_text'    => 'تقنيات حديثة نوظّفها حيث تجعل الأنظمة أكثر أمانًا وذكاءً.',
						'value_2_title'   => 'الاستدامة',
						'value_2_text'    => 'تصاميم عالية الكفاءة وطاقة متجددة تقلّل الأثر على المدى الطويل.',
						'value_3_title'   => 'الموثوقية',
						'value_3_text'    => 'عمل مطابق للأنظمة، مُختبَر وموثّق ويُسلَّم في موعده.',
						'value_4_title'   => 'الإنسان',
						'value_4_text'    => 'فرق معتمدة تُصغي وتتواصل بوضوح وتضع السلامة أولًا.',
						'tagline_1'       => 'نصمّم الراحة بهندسة ذكية',
						'tagline_2'       => 'Engineering Comfort, Perfected.',
						'cta_title'       => 'مستعد للعمل مع سيز؟',
						'cta_text'        => 'لنتحدث عن مشروعك الكهربائي القادم.',
					),
				),
			),

			'solutions' => array(
				'template' => 'page-solutions.php',
				'order'    => 2,
				'en'       => array(
					'title'  => 'Our Solutions',
					'slug'   => 'solutions',
					'fields' => array(
						'header_subtitle' => 'Complete electrical engineering — designed, installed and maintained by one accountable team.',
						'process_eyebrow' => 'How we work',
						'process_title'   => 'Four steps from survey to switch-on.',
						'step_1_title'    => 'Assess',
						'step_1_text'     => 'Site survey, load analysis and a clear scope before design begins.',
						'step_2_title'    => 'Engineer',
						'step_2_text'     => 'Drawings, calculations and equipment selection, checked against code and budget.',
						'step_3_title'    => 'Install',
						'step_3_text'     => 'Certified crews build, test and commission, with full documentation at handover.',
						'step_4_title'    => 'Operate',
						'step_4_text'     => 'Maintenance plans and monitoring that keep systems efficient for years.',
						'faq_eyebrow'     => 'FAQ',
						'faq_title'       => 'Common questions',
						'cta_title'       => 'Not sure which solution fits?',
						'cta_text'        => 'Describe your project and our engineers will recommend the right approach.',
					),
				),
				'ar'       => array(
					'title'  => 'حلولنا',
					'slug'   => 'حلولنا',
					'fields' => array(
						'header_subtitle' => 'هندسة كهربائية متكاملة — تصميم وتركيب وصيانة من فريق واحد مسؤول.',
						'process_eyebrow' => 'كيف نعمل',
						'process_title'   => 'أربع خطوات من المسح الميداني حتى التشغيل.',
						'step_1_title'    => 'التقييم',
						'step_1_text'     => 'مسح الموقع وتحليل الأحمال وتحديد نطاق واضح قبل بدء التصميم.',
						'step_2_title'    => 'الهندسة',
						'step_2_text'     => 'المخططات والحسابات واختيار المعدّات، مع التحقق من مطابقتها للأنظمة والميزانية.',
						'step_3_title'    => 'التركيب',
						'step_3_text'     => 'فرق معتمدة تنفّذ وتختبر وتشغّل، مع توثيق كامل عند التسليم.',
						'step_4_title'    => 'التشغيل',
						'step_4_text'     => 'خطط صيانة ومراقبة تحافظ على كفاءة الأنظمة لسنوات.',
						'faq_eyebrow'     => 'الأسئلة الشائعة',
						'faq_title'       => 'أسئلة متكررة',
						'cta_title'       => 'لست متأكدًا من الحل المناسب؟',
						'cta_text'        => 'صف لنا مشروعك وسيوصي مهندسونا بالنهج الأنسب.',
					),
				),
			),

			'contact' => array(
				'template' => 'page-contact.php',
				'order'    => 4,
				'en'       => array(
					'title'  => 'Contact',
					'slug'   => 'contact',
					'fields' => array(
						'header_subtitle' => 'Tell us about your project — an engineer will get back to you with next steps.',
						'phone'           => '+966 50 326 4000',
						'email'           => 'info@seesengineering.com',
						'address'         => 'Al Shahamah District, Makkah, Saudi Arabia',
						'hours'           => '[Sun – Thu, 00:00 – 00:00]',
						'form_title'      => 'Request a quote',
						'form_text'       => 'Fields marked * are required.',
						'success_title'   => 'Thank you!',
						'success_text'    => 'Your request has been sent. A SEES engineer will contact you shortly.',
					),
				),
				'ar'       => array(
					'title'  => 'تواصل معنا',
					'slug'   => 'تواصل-معنا',
					'fields' => array(
						'header_subtitle' => 'أخبرنا عن مشروعك، وسيتواصل معك أحد مهندسينا بالخطوات التالية.',
						'phone'           => '+966 50 326 4000',
						'email'           => 'info@seesengineering.com',
						'address'         => 'حي الشهامة، مكة المكرمة، المملكة العربية السعودية',
						'hours'           => '[الأحد – الخميس، 00:00 – 00:00]',
						'form_title'      => 'اطلب عرض سعر',
						'form_text'       => 'الحقول المعلَّمة بـ * إلزامية.',
						'success_title'   => 'شكرًا لك!',
						'success_text'    => 'تم إرسال طلبك. سيتواصل معك أحد مهندسي سيز قريبًا.',
					),
				),
			),
		),

		/* ---------------- Solutions: [ icon, accent, en[title, excerpt, intro, bullets], ar[…] ] ---------------- */
		'solutions' => array(
			array(
				'slug' => 'power-distribution', 'icon' => 'bolt', 'accent' => 'teal', 'image' => 'engineer.jpg',
				'en'   => array( 'Power Distribution', 'Switchgear, distribution boards and LV/MV panels — designed, built and commissioned to code.', 'Safe, well-sized distribution is the backbone of every building. We design and build the boards, panels and cabling that carry power where it\'s needed.', "LV / MV switchgear\nDistribution boards\nLoad studies\nTesting & commissioning" ),
				'ar'   => array( 'توزيع الطاقة', 'لوحات المفاتيح ولوحات التوزيع ولوحات الجهد المنخفض والمتوسط — تصميم وتصنيع وتشغيل وفق الأنظمة.', 'التوزيع الآمن والمحسوب بدقة هو العمود الفقري لكل مبنى. نصمّم وننفّذ اللوحات والكابلات التي توصل الطاقة حيث تحتاجها.', "لوحات مفاتيح الجهد المنخفض والمتوسط\nلوحات التوزيع\nدراسات الأحمال\nالاختبار والتشغيل" ),
			),
			array(
				'slug' => 'solar-renewables', 'icon' => 'sun', 'accent' => 'green', 'image' => 'engineer.jpg',
				'en'   => array( 'Solar & Renewables', 'Rooftop and ground-mount PV systems, from yield study and design to grid connection.', 'Clean energy that pays back. From rooftop arrays to ground-mount fields, we handle the full PV lifecycle — yield study, design, installation and grid connection.', "Rooftop PV\nGround-mount arrays\nInverters & storage\nGrid connection" ),
				'ar'   => array( 'الطاقة الشمسية والمتجددة', 'أنظمة كهروضوئية على الأسطح وعلى الأرض، من دراسة الإنتاجية والتصميم حتى الربط بالشبكة.', 'طاقة نظيفة تعود عليك بالفائدة. من المصفوفات على الأسطح إلى المحطات الأرضية، نتولّى دورة حياة النظام الكهروضوئي كاملة — دراسة الإنتاجية والتصميم والتركيب والربط بالشبكة.', "أنظمة الأسطح الشمسية\nالمصفوفات الأرضية\nالعاكسات والتخزين\nالربط بالشبكة" ),
			),
			array(
				'slug' => 'power-infrastructure', 'icon' => 'pylon', 'accent' => 'petrol', 'image' => 'pylons.jpg',
				'en'   => array( 'Power Infrastructure', 'Substations, cabling and overhead lines that bring reliable power to sites and communities.', 'Substations, underground cabling and overhead lines that bring reliable power to developments, sites and communities.', "Substations\nUnderground cabling\nOverhead lines\nStreet lighting" ),
				'ar'   => array( 'البنية التحتية للطاقة', 'محطات فرعية وكابلات وخطوط هوائية توصل طاقة موثوقة إلى المواقع والمجتمعات.', 'محطات فرعية وكابلات أرضية وخطوط هوائية توصل طاقة موثوقة إلى المشاريع والمواقع والمجتمعات.', "المحطات الفرعية\nالكابلات الأرضية\nالخطوط الهوائية\nإنارة الطرق" ),
			),
			array(
				'slug' => 'smart-buildings', 'icon' => 'home', 'accent' => 'teal', 'image' => 'facade.jpg',
				'en'   => array( 'Smart Buildings', 'Lighting control, automation and energy monitoring that cut consumption and simplify operations.', 'Buildings that think. We integrate lighting control, automation and energy monitoring so your facility uses less and runs smoother.', "Lighting control\nBuilding automation\nEnergy monitoring\nLow-current systems" ),
				'ar'   => array( 'المباني الذكية', 'التحكم في الإنارة والأتمتة ومراقبة الطاقة لخفض الاستهلاك وتبسيط التشغيل.', 'مبانٍ تفكّر. ندمج أنظمة التحكم في الإنارة والأتمتة ومراقبة الطاقة لتستهلك منشأتك أقل وتعمل بسلاسة أكبر.', "التحكم في الإنارة\nأتمتة المباني\nمراقبة الطاقة\nأنظمة التيار الخفيف" ),
			),
			array(
				'slug' => 'operations-maintenance', 'icon' => 'wrench', 'accent' => 'green', 'image' => 'sitebanner.jpg',
				'en'   => array( 'Operations & Maintenance', 'Planned maintenance, inspection and fast response that keep systems efficient year after year.', 'Planned maintenance, inspection and fast response that keep your systems efficient — and your operation running — year after year.', "Preventive maintenance\nThermal inspection\nEmergency response\nUpgrades & retrofits" ),
				'ar'   => array( 'التشغيل والصيانة', 'صيانة مخططة وفحص واستجابة سريعة تحافظ على كفاءة الأنظمة عامًا بعد عام.', 'صيانة مخططة وفحص واستجابة سريعة تحافظ على كفاءة أنظمتك واستمرارية أعمالك عامًا بعد عام.', "الصيانة الوقائية\nالفحص الحراري\nالاستجابة للطوارئ\nالتحديث والتطوير" ),
			),
		),

		/* ---------------- FAQs: [ en question, en answer, ar question, ar answer ] ---------------- */
		'faqs' => array(
			array( 'Which areas do you serve?', 'We are based in Makkah and work on projects across [SERVICE AREA]. Get in touch to confirm coverage for your site.',
				'ما المناطق التي تخدمونها؟', 'مقرّنا في مكة المكرمة ونعمل على مشاريع في [منطقة الخدمة]. تواصل معنا للتأكد من تغطية موقعك.' ),
			array( 'Do you handle design and installation together?', 'Yes. One team takes your project from survey and engineering through installation, commissioning and handover, so there is a single point of accountability.',
				'هل تتولّون التصميم والتركيب معًا؟', 'نعم. يتولّى فريق واحد مشروعك من المسح والهندسة إلى التركيب والتشغيل والتسليم، لتكون هناك جهة واحدة مسؤولة.' ),
			array( 'Can you work on existing buildings?', 'Yes — upgrades, retrofits and expansions of existing electrical systems are a core part of our work, including solar added to occupied buildings.',
				'هل يمكنكم العمل على مبانٍ قائمة؟', 'نعم، فتحديث الأنظمة الكهربائية القائمة وتطويرها وتوسعتها جزء أساسي من عملنا، بما في ذلك إضافة الطاقة الشمسية إلى مبانٍ مأهولة.' ),
			array( 'Do you offer maintenance contracts?', 'We offer planned maintenance and inspection programmes tailored to your facility. [Add contract terms or response times here.]',
				'هل تقدّمون عقود صيانة؟', 'نقدّم برامج صيانة وفحص مخططة مصمّمة وفق احتياجات منشأتك. [أضف شروط العقد أو أوقات الاستجابة هنا.]' ),
		),

		/* ---------------- Project categories: slug => [ en, ar ] ---------------- */
		'categories' => array(
			'power-distribution' => array( 'Power Distribution', 'توزيع الطاقة' ),
			'solar'              => array( 'Solar', 'الطاقة الشمسية' ),
			'infrastructure'     => array( 'Infrastructure', 'البنية التحتية' ),
			'smart-buildings'    => array( 'Smart Buildings', 'المباني الذكية' ),
			'maintenance'        => array( 'Maintenance', 'الصيانة' ),
		),

		/* ---------------- Placeholder projects: [ category, image, featured ] ---------------- */
		'projects' => array(
			array( 'solar', 'engineer.jpg', true ),
			array( 'power-distribution', 'engineer.jpg', false ),
			array( 'infrastructure', 'pylons.jpg', false ),
			array( 'smart-buildings', 'facade.jpg', false ),
			array( 'smart-buildings', 'concrete.jpg', false ),
			array( 'maintenance', 'sitebanner.jpg', false ),
			array( 'power-distribution', 'pylons.jpg', false ),
			array( 'solar', 'concrete.jpg', false ),
			array( 'maintenance', 'sign.jpg', false ),
		),
		'project_text' => array(
			'en' => array(
				'title'          => '[PROJECT NAME]',
				'featured_title' => '[PROJECT NAME] — Solar & distribution upgrade',
				'content'        => '<!-- wp:paragraph --><p>[Describe the project: scope, challenge, what SEES delivered and the result.]</p><!-- /wp:paragraph -->',
				'fields'         => array( 'client' => '[Client]', 'city' => '[City]', 'year' => '[Year]', 'summary' => '[One or two sentences on the client\'s challenge and what SEES delivered.]' ),
			),
			'ar' => array(
				'title'          => '[اسم المشروع]',
				'featured_title' => '[اسم المشروع] — تحديث أنظمة الطاقة الشمسية والتوزيع',
				'content'        => '<!-- wp:paragraph --><p>[صف المشروع: النطاق والتحدي وما قدّمته سيز والنتيجة.]</p><!-- /wp:paragraph -->',
				'fields'         => array( 'client' => '[العميل]', 'city' => '[المدينة]', 'year' => '[السنة]', 'summary' => '[جملة أو جملتان عن تحدي العميل وما قدّمته سيز.]' ),
			),
		),

		/* ---------------- Contact Form 7 forms per language ---------------- */
		'forms' => array(
			'en' => array(
				'title'   => 'Quote request (EN)',
				'locale'  => 'en_US',
				'labels'  => array(
					'name' => 'Full name', 'name_ph' => 'Your name', 'company' => 'Company', 'company_ph' => 'Company name',
					'email' => 'Email', 'phone' => 'Phone', 'solution' => 'Solution',
					'options' => '"Power Distribution" "Solar & Renewables" "Power Infrastructure" "Smart Buildings" "Operations & Maintenance"',
					'details' => 'Project details', 'details_ph' => 'Location, size, timeline…', 'submit' => 'Send request', 'subject' => 'Quote request',
				),
				'messages' => array(),
			),
			'ar' => array(
				'title'   => 'طلب عرض سعر (AR)',
				'locale'  => 'ar',
				'labels'  => array(
					'name' => 'الاسم الكامل', 'name_ph' => 'اسمك', 'company' => 'الشركة', 'company_ph' => 'اسم الشركة',
					'email' => 'البريد الإلكتروني', 'phone' => 'رقم الجوال', 'solution' => 'الحل المطلوب',
					'options' => '"توزيع الطاقة" "الطاقة الشمسية والمتجددة" "البنية التحتية للطاقة" "المباني الذكية" "التشغيل والصيانة"',
					'details' => 'تفاصيل المشروع', 'details_ph' => 'الموقع، الحجم، الجدول الزمني…', 'submit' => 'إرسال الطلب', 'subject' => 'طلب عرض سعر',
				),
				// Arabic form messages (so they don't depend on CF7's own language files).
				'messages' => array(
					'mail_sent_ok'     => 'تم إرسال رسالتك بنجاح. شكرًا لك.',
					'mail_sent_ng'     => 'حدث خطأ أثناء إرسال رسالتك. يرجى المحاولة لاحقًا.',
					'validation_error' => 'يوجد خطأ في حقل أو أكثر. يرجى المراجعة والمحاولة مرة أخرى.',
					'spam'             => 'حدث خطأ أثناء إرسال رسالتك. يرجى المحاولة لاحقًا.',
					'accept_terms'     => 'يجب الموافقة على الشروط قبل إرسال رسالتك.',
					'invalid_required' => 'يرجى تعبئة هذا الحقل.',
					'invalid_too_long' => 'هذا الحقل أطول من المسموح.',
					'invalid_too_short'=> 'هذا الحقل أقصر من المسموح.',
					'upload_failed'    => 'حدث خطأ غير معروف أثناء رفع الملف.',
					'invalid_email'    => 'يرجى إدخال بريد إلكتروني صحيح.',
					'invalid_url'      => 'يرجى إدخال رابط صحيح.',
					'invalid_tel'      => 'يرجى إدخال رقم هاتف صحيح.',
					'invalid_number'   => 'يرجى إدخال رقم صحيح.',
					'invalid_date'     => 'يرجى إدخال تاريخ صحيح.',
				),
			),
		),

		/* ---------------- Polylang string translations for Arabic ---------------- */
		'strings' => array(
			'SEES'                                  => 'سيز',
			'Smart Electrical Engineering Solutions' => 'سيز للحلول الهندسية الكهربائية الذكية',
		),
	);
}
