<?php
/**
 * Elementor widget: Nova FAQ Section (repeater).
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * FAQ section Elementor widget.
 */
class Nova_Pet_Elementor_Faq_Section_Widget extends \Elementor\Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'nova_pet_faq_section';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__('Nova FAQ Section', 'nova-pet');
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-accordion';
	}

	/**
	 * @return array<int, string>
	 */
	public function get_categories() {
		return array('general');
	}

	/**
	 * @return array<int, string>
	 */
	public function get_keywords() {
		return array('faq', 'questions', 'accordion', 'nova');
	}

	/**
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_headings',
			array(
				'label' => esc_html__('Headings', 'nova-pet'),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => esc_html__('Title', 'nova-pet'),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__('Questions', 'nova-pet'),
			)
		);

		$this->add_control(
			'subtitle',
			array(
				'label'   => esc_html__('Subtitle', 'nova-pet'),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__(
					'Find answers to common questions from veterinary professionals',
					'nova-pet'
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_items',
			array(
				'label' => esc_html__('FAQ items', 'nova-pet'),
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'question',
			array(
				'label'       => esc_html__('Question', 'nova-pet'),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'answer',
			array(
				'label'       => esc_html__('Answer', 'nova-pet'),
				'type'        => \Elementor\Controls_Manager::WYSIWYG,
				'default'     => '',
			)
		);

		$this->add_control(
			'faqs',
			array(
				'label'       => esc_html__('Items', 'nova-pet'),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'question' => esc_html__('Example question?', 'nova-pet'),
						'answer'   => esc_html__('Example answer text.', 'nova-pet'),
					),
				),
				'title_field' => '{{{ question }}}',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__('Layout', 'nova-pet'),
			)
		);

		$this->add_control(
			'product_id',
			array(
				'label'       => esc_html__('Also load from product (optional)', 'nova-pet'),
				'description' => esc_html__(
					'Appends FAQs from product custom field `nova_product_faqs` after manual items.',
					'nova-pet'
				),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 0,
				'default'     => 0,
			)
		);

		$this->add_control(
			'open_all',
			array(
				'label'        => esc_html__('Open all items by default', 'nova-pet'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Yes', 'nova-pet'),
				'label_off'    => esc_html__('No', 'nova-pet'),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'strip',
			array(
				'label'        => esc_html__('Gray background strip', 'nova-pet'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Yes', 'nova-pet'),
				'label_off'    => esc_html__('No', 'nova-pet'),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'contained',
			array(
				'label'        => esc_html__('Centered container', 'nova-pet'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Yes', 'nova-pet'),
				'label_off'    => esc_html__('No', 'nova-pet'),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		if (empty($settings['faqs']) || !is_array($settings['faqs'])) {
			return;
		}

		$items = array();
		foreach ($settings['faqs'] as $row) {
			$items[] = array(
				'question' => isset($row['question']) ? $row['question'] : '',
				'answer'   => isset($row['answer']) ? $row['answer'] : '',
			);
		}
		$items = nova_pet_normalize_faq_items($items);

		$pid = !empty($settings['product_id']) ? absint($settings['product_id']) : 0;
		if ($pid && function_exists('wc_get_product')) {
			$product = wc_get_product($pid);
			if ($product instanceof WC_Product) {
				$items = array_merge($items, nova_pet_get_product_faq_items($product));
			}
		}

		if (empty($items)) {
			return;
		}

		$html = nova_pet_get_faq_section_html(
			array(
				'items'     => $items,
				'title'     => isset($settings['title']) ? $settings['title'] : '',
				'subtitle'  => isset($settings['subtitle']) ? $settings['subtitle'] : '',
				'open_all'  => !empty($settings['open_all']) && 'yes' === $settings['open_all'],
				'strip'     => !isset($settings['strip']) || 'yes' === $settings['strip'],
				'contained' => !isset($settings['contained']) || 'yes' === $settings['contained'],
				'id_prefix' => 'nova-faq-el-' . (int) $this->get_id(),
			)
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}
}
