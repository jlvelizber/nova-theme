<?php
/**
 * Elementor widget: Nova Feature Cards (repeater, image position, lead).
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Feature cards Elementor widget.
 */
class Nova_Pet_Elementor_Feature_Cards_Widget extends \Elementor\Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'nova_pet_feature_cards';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__('Nova Feature Cards', 'nova-pet');
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	/**
	 * @return array<int, string>
	 */
	public function get_categories() {
		return array('general');
	}

	/**
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__('Layout', 'nova-pet'),
			)
		);

		$this->add_control(
			'cards_only',
			array(
				'label'       => esc_html__('Solo tarjetas (sin contenedor del tema)', 'nova-pet'),
				'description' => esc_html__('Sin section, sin site-container ni rejilla: solo el HTML de cada tarjeta. Usa columnas/secciones de Elementor para colocarlas.', 'nova-pet'),
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'label_on'    => esc_html__('Sí', 'nova-pet'),
				'label_off'   => esc_html__('No', 'nova-pet'),
				'return_value' => 'yes',
				'default'     => '',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_cards',
			array(
				'label' => esc_html__('Cards', 'nova-pet'),
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'image',
			array(
				'label' => esc_html__('Image', 'nova-pet'),
				'type'  => \Elementor\Controls_Manager::MEDIA,
			)
		);

		$repeater->add_control(
			'image_position',
			array(
				'label'   => esc_html__('Image position', 'nova-pet'),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'top',
				'options' => array(
					'top'    => esc_html__('Top (vertical card)', 'nova-pet'),
					'bottom' => esc_html__('Bottom (vertical card)', 'nova-pet'),
					'left'   => esc_html__('Left (horizontal card)', 'nova-pet'),
					'right'  => esc_html__('Right (horizontal card)', 'nova-pet'),
				),
			)
		);

		$repeater->add_control(
			'image_fit',
			array(
				'label'       => esc_html__('Image fit (desktop)', 'nova-pet'),
				'description' => esc_html__('Cover fills the frame (may crop). Contain shows the full image.', 'nova-pet'),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'cover',
				'options'     => array(
					'cover'   => esc_html__('Cover (fill / may crop)', 'nova-pet'),
					'contain' => esc_html__('Contain (full image)', 'nova-pet'),
				),
			)
		);

		$repeater->add_control(
			'image_focus',
			array(
				'label'       => esc_html__('Image focus (desktop)', 'nova-pet'),
				'description' => esc_html__('Which part of the photo stays visible when using Cover.', 'nova-pet'),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'center',
				'options'     => array(
					'center' => esc_html__('Center', 'nova-pet'),
					'top'    => esc_html__('Top', 'nova-pet'),
					'bottom' => esc_html__('Bottom', 'nova-pet'),
					'left'   => esc_html__('Left', 'nova-pet'),
					'right'  => esc_html__('Right', 'nova-pet'),
				),
			)
		);

		$repeater->add_control(
			'mobile_image',
			array(
				'label'       => esc_html__('Imagen en móvil', 'nova-pet'),
				'description' => esc_html__('Completa = sin recorte (proporción natural). Recortar = marco fijo que puedes ajustar.', 'nova-pet'),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'natural',
				'options'     => array(
					'natural' => esc_html__('Completa (sin recorte)', 'nova-pet'),
					'crop'    => esc_html__('Recortar al marco', 'nova-pet'),
				),
			)
		);

		$repeater->add_control(
			'mobile_aspect',
			array(
				'label'     => esc_html__('Proporción del marco (móvil)', 'nova-pet'),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '16-9',
				'options'   => array(
					'16-9' => '16:9',
					'4-3'  => '4:3',
					'1-1'  => '1:1',
					'3-4'  => '3:4 (vertical)',
				),
				'condition' => array(
					'mobile_image' => 'crop',
				),
			)
		);

		$repeater->add_control(
			'mobile_focus',
			array(
				'label'     => esc_html__('Enfoque al recortar (móvil)', 'nova-pet'),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'center',
				'options'   => array(
					'center' => esc_html__('Centro', 'nova-pet'),
					'top'    => esc_html__('Arriba', 'nova-pet'),
					'bottom' => esc_html__('Abajo', 'nova-pet'),
					'left'   => esc_html__('Izquierda', 'nova-pet'),
					'right'  => esc_html__('Derecha', 'nova-pet'),
				),
				'condition' => array(
					'mobile_image' => 'crop',
				),
			)
		);

		$repeater->add_control(
			'lead_card',
			array(
				'label'        => esc_html__('Tall left column (theme grid)', 'nova-pet'),
				'description'  => esc_html__(
					'Actívalo en la primera tarjeta vertical (imagen arriba). Si usas 1 tarjeta vertical + 2 horizontales, el tema también lo aplica automáticamente.',
					'nova-pet'
				),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Yes', 'nova-pet'),
				'label_off'    => esc_html__('No', 'nova-pet'),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$repeater->add_control(
			'label',
			array(
				'label' => esc_html__('Label', 'nova-pet'),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label' => esc_html__('Title', 'nova-pet'),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);

		$repeater->add_control(
			'text',
			array(
				'label' => esc_html__('Description', 'nova-pet'),
				'type'  => \Elementor\Controls_Manager::TEXTAREA,
				'rows'  => 3,
			)
		);

		$repeater->add_control(
			'action',
			array(
				'label'   => esc_html__('Link label', 'nova-pet'),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __('Learn', 'nova-pet'),
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label' => esc_html__('Link URL', 'nova-pet'),
				'type'  => \Elementor\Controls_Manager::URL,
			)
		);

		$this->add_control(
			'cards',
			array(
				'label'       => esc_html__('Cards', 'nova-pet'),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(),
				'title_field' => '{{{ title }}}',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		if (empty($settings['cards']) || !is_array($settings['cards'])) {
			return;
		}

		$cards_only = !empty($settings['cards_only']) && 'yes' === $settings['cards_only'];

		$rows = array();
		foreach ($settings['cards'] as $item) {
			$pos = isset($item['image_position']) ? $item['image_position'] : 'top';
			$url = isset($item['link']['url']) ? $item['link']['url'] : '';

			$img_url = '';
			if (!empty($item['image']['url'])) {
				$img_url = $item['image']['url'];
			}

			$lead = '';
			if (!empty($item['lead_card']) && 'yes' === $item['lead_card']) {
				$lead = 'yes';
			}

			$n = nova_pet_normalize_feature_card(
				array(
					'image'          => $img_url,
					'alt'            => isset($item['title']) ? $item['title'] : '',
					'label'          => isset($item['label']) ? $item['label'] : '',
					'title'          => isset($item['title']) ? $item['title'] : '',
					'text'           => isset($item['text']) ? $item['text'] : '',
					'action'         => isset($item['action']) ? $item['action'] : '',
					'url'            => $url,
					'image_position' => $pos,
					'image_fit'      => isset($item['image_fit']) ? $item['image_fit'] : 'cover',
					'image_focus'    => isset($item['image_focus']) ? $item['image_focus'] : 'center',
					'mobile_image'   => isset($item['mobile_image']) ? $item['mobile_image'] : 'natural',
					'mobile_aspect'  => isset($item['mobile_aspect']) ? $item['mobile_aspect'] : '16-9',
					'mobile_focus'   => isset($item['mobile_focus']) ? $item['mobile_focus'] : 'center',
					'lead'           => $lead,
				)
			);
			if ($n) {
				$rows[] = $n;
			}
		}

		if ($cards_only) {
			foreach ($rows as $row) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo nova_pet_get_single_card_html($row);
			}
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo nova_pet_get_feature_cards_html($rows);
	}
}
