<?php
/**
 * Theme Customizer Settings
 *
 * Adds customizer panels, sections, and settings.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register customizer settings
 *
 * @since 1.0.0
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function ngs_customize_register( $wp_customize ) {

	// Add NGS Settings Section
	$wp_customize->add_section( 'ngs_settings', array(
		'title'       => esc_html__( 'NGS Settings', 'ngs-designsystem' ),
		'description' => esc_html__( 'Configure NGS Smart Home theme options', 'ngs-designsystem' ),
		'priority'    => 30,
	) );

	// WhatsApp Number
	$wp_customize->add_setting( 'ngs_whatsapp_number', array(
		'default'           => '+966',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ngs_whatsapp_number', array(
		'label'       => esc_html__( 'WhatsApp Number', 'ngs-designsystem' ),
		'description' => esc_html__( 'Enter WhatsApp number with country code (e.g., +966501234567)', 'ngs-designsystem' ),
		'section'     => 'ngs_settings',
		'type'        => 'text',
	) );

	// Instagram URL
	$wp_customize->add_setting( 'ngs_instagram_url', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ngs_instagram_url', array(
		'label'       => esc_html__( 'Instagram URL', 'ngs-designsystem' ),
		'description' => esc_html__( 'Full Instagram profile URL', 'ngs-designsystem' ),
		'section'     => 'ngs_settings',
		'type'        => 'url',
	) );

	// Twitter/X URL
	$wp_customize->add_setting( 'ngs_twitter_url', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ngs_twitter_url', array(
		'label'       => esc_html__( 'Twitter/X URL', 'ngs-designsystem' ),
		'description' => esc_html__( 'Full Twitter/X profile URL', 'ngs-designsystem' ),
		'section'     => 'ngs_settings',
		'type'        => 'url',
	) );

	// YouTube URL
	$wp_customize->add_setting( 'ngs_youtube_url', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ngs_youtube_url', array(
		'label'       => esc_html__( 'YouTube URL', 'ngs-designsystem' ),
		'description' => esc_html__( 'Full YouTube channel URL', 'ngs-designsystem' ),
		'section'     => 'ngs_settings',
		'type'        => 'url',
	) );

	// TikTok URL
	$wp_customize->add_setting( 'ngs_tiktok_url', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ngs_tiktok_url', array(
		'label'       => esc_html__( 'TikTok URL', 'ngs-designsystem' ),
		'description' => esc_html__( 'Full TikTok profile URL', 'ngs-designsystem' ),
		'section'     => 'ngs_settings',
		'type'        => 'url',
	) );

	// Company CR Number
	$wp_customize->add_setting( 'ngs_cr_number', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ngs_cr_number', array(
		'label'       => esc_html__( 'Company CR Number', 'ngs-designsystem' ),
		'description' => esc_html__( 'Commercial Registration number', 'ngs-designsystem' ),
		'section'     => 'ngs_settings',
		'type'        => 'text',
	) );

	// VAT Number
	$wp_customize->add_setting( 'ngs_vat_number', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ngs_vat_number', array(
		'label'       => esc_html__( 'VAT Number', 'ngs-designsystem' ),
		'description' => esc_html__( 'VAT registration number', 'ngs-designsystem' ),
		'section'     => 'ngs_settings',
		'type'        => 'text',
	) );

	// Support Email
	$wp_customize->add_setting( 'ngs_support_email', array(
		'default'           => get_option( 'admin_email' ),
		'sanitize_callback' => 'sanitize_email',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ngs_support_email', array(
		'label'       => esc_html__( 'Support Email', 'ngs-designsystem' ),
		'description' => esc_html__( 'Customer support email address', 'ngs-designsystem' ),
		'section'     => 'ngs_settings',
		'type'        => 'email',
	) );
}
add_action( 'customize_register', 'ngs_customize_register' );
