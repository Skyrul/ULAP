<?php

/**
 * This is the model class for table "{{campaigns}}".
 *
 * The followings are the available columns in table '{{campaigns}}':
 * @property string $campaign_id
 * @property string $campaign_name
 * @property string $active
 * @property string $dial_status_a
 * @property string $dial_status_b
 * @property string $dial_status_c
 * @property string $dial_status_d
 * @property string $dial_status_e
 * @property string $lead_order
 * @property string $park_ext
 * @property string $park_file_name
 * @property string $web_form_address
 * @property string $allow_closers
 * @property string $hopper_level
 * @property string $auto_dial_level
 * @property string $next_agent_call
 * @property string $local_call_time
 * @property string $voicemail_ext
 * @property integer $dial_timeout
 * @property string $dial_prefix
 * @property string $campaign_cid
 * @property string $campaign_vdad_exten
 * @property string $campaign_rec_exten
 * @property string $campaign_recording
 * @property string $campaign_rec_filename
 * @property string $campaign_script
 * @property string $get_call_launch
 * @property string $am_message_exten
 * @property string $amd_send_to_vmx
 * @property string $xferconf_a_dtmf
 * @property string $xferconf_a_number
 * @property string $xferconf_b_dtmf
 * @property string $xferconf_b_number
 * @property string $alt_number_dialing
 * @property string $scheduled_callbacks
 * @property string $lead_filter_id
 * @property integer $drop_call_seconds
 * @property string $drop_action
 * @property string $safe_harbor_exten
 * @property string $display_dialable_count
 * @property integer $wrapup_seconds
 * @property string $wrapup_message
 * @property string $closer_campaigns
 * @property string $use_internal_dnc
 * @property integer $allcalls_delay
 * @property string $omit_phone_code
 * @property string $dial_method
 * @property string $available_only_ratio_tally
 * @property string $adaptive_dropped_percentage
 * @property string $adaptive_maximum_level
 * @property string $adaptive_latest_server_time
 * @property string $adaptive_intensity
 * @property integer $adaptive_dl_diff_target
 * @property string $concurrent_transfers
 * @property string $auto_alt_dial
 * @property string $auto_alt_dial_statuses
 * @property string $agent_pause_codes_active
 * @property string $campaign_description
 * @property string $campaign_changedate
 * @property string $campaign_stats_refresh
 * @property string $campaign_logindate
 * @property string $dial_statuses
 * @property string $disable_alter_custdata
 * @property string $no_hopper_leads_logins
 * @property string $list_order_mix
 * @property string $campaign_allow_inbound
 * @property string $manual_dial_list_id
 * @property string $default_xfer_group
 * @property string $xfer_groups
 * @property integer $queue_priority
 * @property string $drop_inbound_group
 * @property string $qc_enabled
 * @property string $qc_statuses
 * @property string $qc_lists
 * @property string $qc_shift_id
 * @property string $qc_get_record_launch
 * @property string $qc_show_recording
 * @property string $qc_web_form_address
 * @property string $qc_script
 * @property string $survey_first_audio_file
 * @property string $survey_dtmf_digits
 * @property string $survey_ni_digit
 * @property string $survey_opt_in_audio_file
 * @property string $survey_ni_audio_file
 * @property string $survey_method
 * @property string $survey_no_response_action
 * @property string $survey_ni_status
 * @property string $survey_response_digit_map
 * @property string $survey_xfer_exten
 * @property string $survey_camp_record_dir
 * @property string $disable_alter_custphone
 * @property string $display_queue_count
 * @property string $manual_dial_filter
 * @property string $agent_clipboard_copy
 * @property string $agent_extended_alt_dial
 * @property string $use_campaign_dnc
 * @property string $three_way_call_cid
 * @property string $three_way_dial_prefix
 * @property string $web_form_target
 * @property string $vtiger_search_category
 * @property string $vtiger_create_call_record
 * @property string $vtiger_create_lead_record
 * @property string $vtiger_screen_login
 * @property string $cpd_amd_action
 * @property string $agent_allow_group_alias
 * @property string $default_group_alias
 * @property string $vtiger_search_dead
 * @property string $vtiger_status_call
 * @property string $survey_third_digit
 * @property string $survey_third_audio_file
 * @property string $survey_third_status
 * @property string $survey_third_exten
 * @property string $survey_fourth_digit
 * @property string $survey_fourth_audio_file
 * @property string $survey_fourth_status
 * @property string $survey_fourth_exten
 * @property string $drop_lockout_time
 * @property string $quick_transfer_button
 * @property string $prepopulate_transfer_preset
 * @property string $drop_rate_group
 * @property string $view_calls_in_queue
 * @property string $view_calls_in_queue_launch
 * @property string $grab_calls_in_queue
 * @property string $call_requeue_button
 * @property string $pause_after_each_call
 * @property string $no_hopper_dialing
 * @property string $agent_dial_owner_only
 * @property string $agent_display_dialable_leads
 * @property string $web_form_address_two
 * @property string $waitforsilence_options
 * @property string $agent_select_territories
 * @property string $campaign_calldate
 * @property string $crm_popup_login
 * @property string $crm_login_address
 * @property string $timer_action
 * @property string $timer_action_message
 * @property integer $timer_action_seconds
 * @property string $start_call_url
 * @property string $dispo_call_url
 * @property string $xferconf_c_number
 * @property string $xferconf_d_number
 * @property string $xferconf_e_number
 * @property string $use_custom_cid
 * @property string $scheduled_callbacks_alert
 * @property string $queuemetrics_callstatus_override
 * @property string $extension_appended_cidname
 * @property string $scheduled_callbacks_count
 * @property string $manual_dial_override
 * @property string $blind_monitor_warning
 * @property string $blind_monitor_message
 * @property string $blind_monitor_filename
 * @property string $inbound_queue_no_dial
 * @property string $timer_action_destination
 * @property string $enable_xfer_presets
 * @property string $hide_xfer_number_to_dial
 * @property string $manual_dial_prefix
 * @property string $customer_3way_hangup_logging
 * @property integer $customer_3way_hangup_seconds
 * @property string $customer_3way_hangup_action
 * @property string $ivr_park_call
 * @property string $ivr_park_call_agi
 * @property string $manual_preview_dial
 * @property string $realtime_agent_time_stats
 * @property string $use_auto_hopper
 * @property string $auto_hopper_multi
 * @property integer $auto_hopper_level
 * @property string $auto_trim_hopper
 * @property string $api_manual_dial
 * @property string $manual_dial_call_time_check
 * @property string $display_leads_count
 * @property string $lead_order_randomize
 * @property string $lead_order_secondary
 * @property string $per_call_notes
 * @property string $my_callback_option
 * @property string $agent_lead_search
 * @property string $agent_lead_search_method
 * @property string $queuemetrics_phone_environment
 * @property string $auto_pause_precall
 * @property string $auto_pause_precall_code
 * @property string $auto_resume_precall
 * @property string $manual_dial_cid
 * @property string $post_phone_time_diff_alert
 * @property string $custom_3way_button_transfer
 * @property string $available_only_tally_threshold
 * @property integer $available_only_tally_threshold_agents
 * @property string $dial_level_threshold
 * @property integer $dial_level_threshold_agents
 * @property string $safe_harbor_audio
 * @property string $safe_harbor_menu_id
 * @property string $survey_menu_id
 * @property integer $callback_days_limit
 * @property string $dl_diff_target_method
 * @property string $disable_dispo_screen
 * @property string $disable_dispo_status
 * @property string $screen_labels
 * @property string $status_display_fields
 * @property string $na_call_url
 * @property string $survey_recording
 * @property string $pllb_grouping
 * @property integer $pllb_grouping_limit
 * @property integer $call_count_limit
 * @property integer $call_count_target
 * @property integer $callback_hours_block
 * @property string $callback_list_calltime
 * @property string $user_group
 * @property string $hopper_vlc_dup_check
 * @property string $in_group_dial
 * @property string $in_group_dial_select
 * @property string $safe_harbor_audio_field
 * @property string $pause_after_next_call
 * @property string $owner_populate
 * @property string $use_other_campaign_dnc
 * @property string $allow_emails
 * @property string $amd_inbound_group
 * @property string $amd_callmenu
 * @property integer $survey_wait_sec
 * @property string $manual_dial_lead_id
 * @property integer $dead_max
 * @property string $dead_max_dispo
 * @property integer $dispo_max
 * @property string $dispo_max_dispo
 * @property integer $pause_max
 * @property integer $max_inbound_calls
 * @property string $manual_dial_search_checkbox
 * @property string $hide_call_log_info
 * @property integer $timer_alt_seconds
 */
class VicidialCampaigns extends MyActiveRecord
{
	public function getDbConnection()
    {
        return self::getExternalDbConnection();
    }
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaigns}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('campaign_id', 'required'),
			array('dial_timeout, drop_call_seconds, wrapup_seconds, allcalls_delay, adaptive_dl_diff_target, queue_priority, timer_action_seconds, customer_3way_hangup_seconds, auto_hopper_level, available_only_tally_threshold_agents, dial_level_threshold_agents, callback_days_limit, pllb_grouping_limit, call_count_limit, call_count_target, callback_hours_block, survey_wait_sec, dead_max, dispo_max, pause_max, max_inbound_calls, timer_alt_seconds', 'numerical', 'integerOnly'=>true),
			array('campaign_id, hopper_level, campaign_recording, use_internal_dnc, use_campaign_dnc, cpd_amd_action, prepopulate_transfer_preset, use_custom_cid, queuemetrics_callstatus_override, enable_xfer_presets, hide_xfer_number_to_dial, customer_3way_hangup_logging, manual_dial_call_time_check, per_call_notes, callback_list_calltime, pause_after_next_call, owner_populate, use_other_campaign_dnc', 'length', 'max'=>8),
			array('campaign_name', 'length', 'max'=>40),
			array('active, allow_closers, amd_send_to_vmx, scheduled_callbacks, display_dialable_count, omit_phone_code, available_only_ratio_tally, campaign_stats_refresh, disable_alter_custdata, no_hopper_leads_logins, campaign_allow_inbound, qc_enabled, qc_show_recording, survey_ni_digit, display_queue_count, agent_extended_alt_dial, vtiger_create_lead_record, agent_allow_group_alias, vtiger_status_call, survey_third_digit, survey_fourth_digit, grab_calls_in_queue, call_requeue_button, pause_after_each_call, no_hopper_dialing, agent_display_dialable_leads, agent_select_territories, crm_popup_login, extension_appended_cidname, use_auto_hopper, auto_trim_hopper, display_leads_count, lead_order_randomize, auto_pause_precall, auto_resume_precall, hopper_vlc_dup_check, allow_emails, manual_dial_lead_id, hide_call_log_info', 'length', 'max'=>1),
			array('dial_status_a, dial_status_b, dial_status_c, dial_status_d, dial_status_e, auto_dial_level, adaptive_maximum_level, adaptive_intensity, survey_no_response_action, survey_ni_status, survey_third_status, survey_fourth_status, drop_lockout_time, view_calls_in_queue_launch, auto_hopper_multi, auto_pause_precall_code, disable_dispo_status, dead_max_dispo, dispo_max_dispo', 'length', 'max'=>6),
			array('lead_order, default_group_alias, timer_action_destination, agent_lead_search_method, post_phone_time_diff_alert, custom_3way_button_transfer, status_display_fields, safe_harbor_audio_field', 'length', 'max'=>30),
			array('park_ext, local_call_time, voicemail_ext, campaign_script, get_call_launch, lead_filter_id, qc_script, vtiger_screen_login, scheduled_callbacks_count, survey_recording', 'length', 'max'=>10),
			array('park_file_name, am_message_exten, web_form_target, vtiger_search_category, blind_monitor_filename, safe_harbor_audio', 'length', 'max'=>100),
			array('next_agent_call, ivr_park_call, disable_dispo_screen', 'length', 'max'=>21),
			array('dial_prefix, campaign_cid, campaign_vdad_exten, campaign_rec_exten, alt_number_dialing, safe_harbor_exten, list_order_mix, default_xfer_group, drop_inbound_group, qc_shift_id, survey_xfer_exten, three_way_dial_prefix, survey_third_exten, survey_fourth_exten, quick_transfer_button, drop_rate_group, timer_action, manual_dial_prefix, queuemetrics_phone_environment, screen_labels, user_group, amd_inbound_group', 'length', 'max'=>20),
			array('campaign_rec_filename, xferconf_a_dtmf, xferconf_a_number, xferconf_b_dtmf, xferconf_b_number, survey_first_audio_file, survey_opt_in_audio_file, survey_ni_audio_file, manual_dial_filter, agent_clipboard_copy, survey_third_audio_file, survey_fourth_audio_file, xferconf_c_number, xferconf_d_number, xferconf_e_number, safe_harbor_menu_id, survey_menu_id, amd_callmenu', 'length', 'max'=>50),
			array('drop_action', 'length', 'max'=>13),
			array('wrapup_message, auto_alt_dial_statuses, campaign_description, dial_statuses, qc_web_form_address, survey_response_digit_map, survey_camp_record_dir, timer_action_message, blind_monitor_message', 'length', 'max'=>255),
			array('dial_method, survey_dtmf_digits, agent_dial_owner_only, manual_preview_dial, lead_order_secondary, manual_dial_search_checkbox', 'length', 'max'=>16),
			array('adaptive_dropped_percentage, adaptive_latest_server_time, concurrent_transfers, disable_alter_custphone, view_calls_in_queue', 'length', 'max'=>4),
			array('auto_alt_dial', 'length', 'max'=>26),
			array('agent_pause_codes_active, vtiger_create_call_record, customer_3way_hangup_action', 'length', 'max'=>5),
			array('manual_dial_list_id, survey_method', 'length', 'max'=>14),
			array('qc_get_record_launch, vtiger_search_dead, my_callback_option', 'length', 'max'=>9),
			array('three_way_call_cid, blind_monitor_warning', 'length', 'max'=>12),
			array('waitforsilence_options, realtime_agent_time_stats', 'length', 'max'=>25),
			array('scheduled_callbacks_alert, dl_diff_target_method, pllb_grouping', 'length', 'max'=>15),
			array('manual_dial_override, inbound_queue_no_dial, manual_dial_cid, in_group_dial', 'length', 'max'=>11),
			array('api_manual_dial', 'length', 'max'=>18),
			array('agent_lead_search', 'length', 'max'=>28),
			array('available_only_tally_threshold, dial_level_threshold, in_group_dial_select', 'length', 'max'=>17),
			array('web_form_address, closer_campaigns, campaign_changedate, campaign_logindate, xfer_groups, qc_statuses, qc_lists, web_form_address_two, campaign_calldate, crm_login_address, start_call_url, dispo_call_url, ivr_park_call_agi, na_call_url', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('campaign_id, campaign_name, active, dial_status_a, dial_status_b, dial_status_c, dial_status_d, dial_status_e, lead_order, park_ext, park_file_name, web_form_address, allow_closers, hopper_level, auto_dial_level, next_agent_call, local_call_time, voicemail_ext, dial_timeout, dial_prefix, campaign_cid, campaign_vdad_exten, campaign_rec_exten, campaign_recording, campaign_rec_filename, campaign_script, get_call_launch, am_message_exten, amd_send_to_vmx, xferconf_a_dtmf, xferconf_a_number, xferconf_b_dtmf, xferconf_b_number, alt_number_dialing, scheduled_callbacks, lead_filter_id, drop_call_seconds, drop_action, safe_harbor_exten, display_dialable_count, wrapup_seconds, wrapup_message, closer_campaigns, use_internal_dnc, allcalls_delay, omit_phone_code, dial_method, available_only_ratio_tally, adaptive_dropped_percentage, adaptive_maximum_level, adaptive_latest_server_time, adaptive_intensity, adaptive_dl_diff_target, concurrent_transfers, auto_alt_dial, auto_alt_dial_statuses, agent_pause_codes_active, campaign_description, campaign_changedate, campaign_stats_refresh, campaign_logindate, dial_statuses, disable_alter_custdata, no_hopper_leads_logins, list_order_mix, campaign_allow_inbound, manual_dial_list_id, default_xfer_group, xfer_groups, queue_priority, drop_inbound_group, qc_enabled, qc_statuses, qc_lists, qc_shift_id, qc_get_record_launch, qc_show_recording, qc_web_form_address, qc_script, survey_first_audio_file, survey_dtmf_digits, survey_ni_digit, survey_opt_in_audio_file, survey_ni_audio_file, survey_method, survey_no_response_action, survey_ni_status, survey_response_digit_map, survey_xfer_exten, survey_camp_record_dir, disable_alter_custphone, display_queue_count, manual_dial_filter, agent_clipboard_copy, agent_extended_alt_dial, use_campaign_dnc, three_way_call_cid, three_way_dial_prefix, web_form_target, vtiger_search_category, vtiger_create_call_record, vtiger_create_lead_record, vtiger_screen_login, cpd_amd_action, agent_allow_group_alias, default_group_alias, vtiger_search_dead, vtiger_status_call, survey_third_digit, survey_third_audio_file, survey_third_status, survey_third_exten, survey_fourth_digit, survey_fourth_audio_file, survey_fourth_status, survey_fourth_exten, drop_lockout_time, quick_transfer_button, prepopulate_transfer_preset, drop_rate_group, view_calls_in_queue, view_calls_in_queue_launch, grab_calls_in_queue, call_requeue_button, pause_after_each_call, no_hopper_dialing, agent_dial_owner_only, agent_display_dialable_leads, web_form_address_two, waitforsilence_options, agent_select_territories, campaign_calldate, crm_popup_login, crm_login_address, timer_action, timer_action_message, timer_action_seconds, start_call_url, dispo_call_url, xferconf_c_number, xferconf_d_number, xferconf_e_number, use_custom_cid, scheduled_callbacks_alert, queuemetrics_callstatus_override, extension_appended_cidname, scheduled_callbacks_count, manual_dial_override, blind_monitor_warning, blind_monitor_message, blind_monitor_filename, inbound_queue_no_dial, timer_action_destination, enable_xfer_presets, hide_xfer_number_to_dial, manual_dial_prefix, customer_3way_hangup_logging, customer_3way_hangup_seconds, customer_3way_hangup_action, ivr_park_call, ivr_park_call_agi, manual_preview_dial, realtime_agent_time_stats, use_auto_hopper, auto_hopper_multi, auto_hopper_level, auto_trim_hopper, api_manual_dial, manual_dial_call_time_check, display_leads_count, lead_order_randomize, lead_order_secondary, per_call_notes, my_callback_option, agent_lead_search, agent_lead_search_method, queuemetrics_phone_environment, auto_pause_precall, auto_pause_precall_code, auto_resume_precall, manual_dial_cid, post_phone_time_diff_alert, custom_3way_button_transfer, available_only_tally_threshold, available_only_tally_threshold_agents, dial_level_threshold, dial_level_threshold_agents, safe_harbor_audio, safe_harbor_menu_id, survey_menu_id, callback_days_limit, dl_diff_target_method, disable_dispo_screen, disable_dispo_status, screen_labels, status_display_fields, na_call_url, survey_recording, pllb_grouping, pllb_grouping_limit, call_count_limit, call_count_target, callback_hours_block, callback_list_calltime, user_group, hopper_vlc_dup_check, in_group_dial, in_group_dial_select, safe_harbor_audio_field, pause_after_next_call, owner_populate, use_other_campaign_dnc, allow_emails, amd_inbound_group, amd_callmenu, survey_wait_sec, manual_dial_lead_id, dead_max, dead_max_dispo, dispo_max, dispo_max_dispo, pause_max, max_inbound_calls, manual_dial_search_checkbox, hide_call_log_info, timer_alt_seconds', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'campaign_id' => 'Campaign',
			'campaign_name' => 'Campaign Name',
			'active' => 'Active',
			'dial_status_a' => 'Dial Status A',
			'dial_status_b' => 'Dial Status B',
			'dial_status_c' => 'Dial Status C',
			'dial_status_d' => 'Dial Status D',
			'dial_status_e' => 'Dial Status E',
			'lead_order' => 'Lead Order',
			'park_ext' => 'Park Ext',
			'park_file_name' => 'Park File Name',
			'web_form_address' => 'Web Form Address',
			'allow_closers' => 'Allow Closers',
			'hopper_level' => 'Hopper Level',
			'auto_dial_level' => 'Auto Dial Level',
			'next_agent_call' => 'Next Agent Call',
			'local_call_time' => 'Local Call Time',
			'voicemail_ext' => 'Voicemail Ext',
			'dial_timeout' => 'Dial Timeout',
			'dial_prefix' => 'Dial Prefix',
			'campaign_cid' => 'Campaign Cid',
			'campaign_vdad_exten' => 'Campaign Vdad Exten',
			'campaign_rec_exten' => 'Campaign Rec Exten',
			'campaign_recording' => 'Campaign Recording',
			'campaign_rec_filename' => 'Campaign Rec Filename',
			'campaign_script' => 'Campaign Script',
			'get_call_launch' => 'Get Call Launch',
			'am_message_exten' => 'Am Message Exten',
			'amd_send_to_vmx' => 'Amd Send To Vmx',
			'xferconf_a_dtmf' => 'Xferconf A Dtmf',
			'xferconf_a_number' => 'Xferconf A Number',
			'xferconf_b_dtmf' => 'Xferconf B Dtmf',
			'xferconf_b_number' => 'Xferconf B Number',
			'alt_number_dialing' => 'Alt Number Dialing',
			'scheduled_callbacks' => 'Scheduled Callbacks',
			'lead_filter_id' => 'Lead Filter',
			'drop_call_seconds' => 'Drop Call Seconds',
			'drop_action' => 'Drop Action',
			'safe_harbor_exten' => 'Safe Harbor Exten',
			'display_dialable_count' => 'Display Dialable Count',
			'wrapup_seconds' => 'Wrapup Seconds',
			'wrapup_message' => 'Wrapup Message',
			'closer_campaigns' => 'Closer Campaigns',
			'use_internal_dnc' => 'Use Internal Dnc',
			'allcalls_delay' => 'Allcalls Delay',
			'omit_phone_code' => 'Omit Phone Code',
			'dial_method' => 'Dial Method',
			'available_only_ratio_tally' => 'Available Only Ratio Tally',
			'adaptive_dropped_percentage' => 'Adaptive Dropped Percentage',
			'adaptive_maximum_level' => 'Adaptive Maximum Level',
			'adaptive_latest_server_time' => 'Adaptive Latest Server Time',
			'adaptive_intensity' => 'Adaptive Intensity',
			'adaptive_dl_diff_target' => 'Adaptive Dl Diff Target',
			'concurrent_transfers' => 'Concurrent Transfers',
			'auto_alt_dial' => 'Auto Alt Dial',
			'auto_alt_dial_statuses' => 'Auto Alt Dial Statuses',
			'agent_pause_codes_active' => 'Agent Pause Codes Active',
			'campaign_description' => 'Campaign Description',
			'campaign_changedate' => 'Campaign Changedate',
			'campaign_stats_refresh' => 'Campaign Stats Refresh',
			'campaign_logindate' => 'Campaign Logindate',
			'dial_statuses' => 'Dial Statuses',
			'disable_alter_custdata' => 'Disable Alter Custdata',
			'no_hopper_leads_logins' => 'No Hopper Leads Logins',
			'list_order_mix' => 'List Order Mix',
			'campaign_allow_inbound' => 'Campaign Allow Inbound',
			'manual_dial_list_id' => 'Manual Dial List',
			'default_xfer_group' => 'Default Xfer Group',
			'xfer_groups' => 'Xfer Groups',
			'queue_priority' => 'Queue Priority',
			'drop_inbound_group' => 'Drop Inbound Group',
			'qc_enabled' => 'Qc Enabled',
			'qc_statuses' => 'Qc Statuses',
			'qc_lists' => 'Qc Lists',
			'qc_shift_id' => 'Qc Shift',
			'qc_get_record_launch' => 'Qc Get Record Launch',
			'qc_show_recording' => 'Qc Show Recording',
			'qc_web_form_address' => 'Qc Web Form Address',
			'qc_script' => 'Qc Script',
			'survey_first_audio_file' => 'Survey First Audio File',
			'survey_dtmf_digits' => 'Survey Dtmf Digits',
			'survey_ni_digit' => 'Survey Ni Digit',
			'survey_opt_in_audio_file' => 'Survey Opt In Audio File',
			'survey_ni_audio_file' => 'Survey Ni Audio File',
			'survey_method' => 'Survey Method',
			'survey_no_response_action' => 'Survey No Response Action',
			'survey_ni_status' => 'Survey Ni Status',
			'survey_response_digit_map' => 'Survey Response Digit Map',
			'survey_xfer_exten' => 'Survey Xfer Exten',
			'survey_camp_record_dir' => 'Survey Camp Record Dir',
			'disable_alter_custphone' => 'Disable Alter Custphone',
			'display_queue_count' => 'Display Queue Count',
			'manual_dial_filter' => 'Manual Dial Filter',
			'agent_clipboard_copy' => 'Agent Clipboard Copy',
			'agent_extended_alt_dial' => 'Agent Extended Alt Dial',
			'use_campaign_dnc' => 'Use Campaign Dnc',
			'three_way_call_cid' => 'Three Way Call Cid',
			'three_way_dial_prefix' => 'Three Way Dial Prefix',
			'web_form_target' => 'Web Form Target',
			'vtiger_search_category' => 'Vtiger Search Category',
			'vtiger_create_call_record' => 'Vtiger Create Call Record',
			'vtiger_create_lead_record' => 'Vtiger Create Lead Record',
			'vtiger_screen_login' => 'Vtiger Screen Login',
			'cpd_amd_action' => 'Cpd Amd Action',
			'agent_allow_group_alias' => 'Agent Allow Group Alias',
			'default_group_alias' => 'Default Group Alias',
			'vtiger_search_dead' => 'Vtiger Search Dead',
			'vtiger_status_call' => 'Vtiger Status Call',
			'survey_third_digit' => 'Survey Third Digit',
			'survey_third_audio_file' => 'Survey Third Audio File',
			'survey_third_status' => 'Survey Third Status',
			'survey_third_exten' => 'Survey Third Exten',
			'survey_fourth_digit' => 'Survey Fourth Digit',
			'survey_fourth_audio_file' => 'Survey Fourth Audio File',
			'survey_fourth_status' => 'Survey Fourth Status',
			'survey_fourth_exten' => 'Survey Fourth Exten',
			'drop_lockout_time' => 'Drop Lockout Time',
			'quick_transfer_button' => 'Quick Transfer Button',
			'prepopulate_transfer_preset' => 'Prepopulate Transfer Preset',
			'drop_rate_group' => 'Drop Rate Group',
			'view_calls_in_queue' => 'View Calls In Queue',
			'view_calls_in_queue_launch' => 'View Calls In Queue Launch',
			'grab_calls_in_queue' => 'Grab Calls In Queue',
			'call_requeue_button' => 'Call Requeue Button',
			'pause_after_each_call' => 'Pause After Each Call',
			'no_hopper_dialing' => 'No Hopper Dialing',
			'agent_dial_owner_only' => 'Agent Dial Owner Only',
			'agent_display_dialable_leads' => 'Agent Display Dialable Leads',
			'web_form_address_two' => 'Web Form Address Two',
			'waitforsilence_options' => 'Waitforsilence Options',
			'agent_select_territories' => 'Agent Select Territories',
			'campaign_calldate' => 'Campaign Calldate',
			'crm_popup_login' => 'Crm Popup Login',
			'crm_login_address' => 'Crm Login Address',
			'timer_action' => 'Timer Action',
			'timer_action_message' => 'Timer Action Message',
			'timer_action_seconds' => 'Timer Action Seconds',
			'start_call_url' => 'Start Call Url',
			'dispo_call_url' => 'Dispo Call Url',
			'xferconf_c_number' => 'Xferconf C Number',
			'xferconf_d_number' => 'Xferconf D Number',
			'xferconf_e_number' => 'Xferconf E Number',
			'use_custom_cid' => 'Use Custom Cid',
			'scheduled_callbacks_alert' => 'Scheduled Callbacks Alert',
			'queuemetrics_callstatus_override' => 'Queuemetrics Callstatus Override',
			'extension_appended_cidname' => 'Extension Appended Cidname',
			'scheduled_callbacks_count' => 'Scheduled Callbacks Count',
			'manual_dial_override' => 'Manual Dial Override',
			'blind_monitor_warning' => 'Blind Monitor Warning',
			'blind_monitor_message' => 'Blind Monitor Message',
			'blind_monitor_filename' => 'Blind Monitor Filename',
			'inbound_queue_no_dial' => 'Inbound Queue No Dial',
			'timer_action_destination' => 'Timer Action Destination',
			'enable_xfer_presets' => 'Enable Xfer Presets',
			'hide_xfer_number_to_dial' => 'Hide Xfer Number To Dial',
			'manual_dial_prefix' => 'Manual Dial Prefix',
			'customer_3way_hangup_logging' => 'Customer 3way Hangup Logging',
			'customer_3way_hangup_seconds' => 'Customer 3way Hangup Seconds',
			'customer_3way_hangup_action' => 'Customer 3way Hangup Action',
			'ivr_park_call' => 'Ivr Park Call',
			'ivr_park_call_agi' => 'Ivr Park Call Agi',
			'manual_preview_dial' => 'Manual Preview Dial',
			'realtime_agent_time_stats' => 'Realtime Agent Time Stats',
			'use_auto_hopper' => 'Use Auto Hopper',
			'auto_hopper_multi' => 'Auto Hopper Multi',
			'auto_hopper_level' => 'Auto Hopper Level',
			'auto_trim_hopper' => 'Auto Trim Hopper',
			'api_manual_dial' => 'Api Manual Dial',
			'manual_dial_call_time_check' => 'Manual Dial Call Time Check',
			'display_leads_count' => 'Display Leads Count',
			'lead_order_randomize' => 'Lead Order Randomize',
			'lead_order_secondary' => 'Lead Order Secondary',
			'per_call_notes' => 'Per Call Notes',
			'my_callback_option' => 'My Callback Option',
			'agent_lead_search' => 'Agent Lead Search',
			'agent_lead_search_method' => 'Agent Lead Search Method',
			'queuemetrics_phone_environment' => 'Queuemetrics Phone Environment',
			'auto_pause_precall' => 'Auto Pause Precall',
			'auto_pause_precall_code' => 'Auto Pause Precall Code',
			'auto_resume_precall' => 'Auto Resume Precall',
			'manual_dial_cid' => 'Manual Dial Cid',
			'post_phone_time_diff_alert' => 'Post Phone Time Diff Alert',
			'custom_3way_button_transfer' => 'Custom 3way Button Transfer',
			'available_only_tally_threshold' => 'Available Only Tally Threshold',
			'available_only_tally_threshold_agents' => 'Available Only Tally Threshold Agents',
			'dial_level_threshold' => 'Dial Level Threshold',
			'dial_level_threshold_agents' => 'Dial Level Threshold Agents',
			'safe_harbor_audio' => 'Safe Harbor Audio',
			'safe_harbor_menu_id' => 'Safe Harbor Menu',
			'survey_menu_id' => 'Survey Menu',
			'callback_days_limit' => 'Callback Days Limit',
			'dl_diff_target_method' => 'Dl Diff Target Method',
			'disable_dispo_screen' => 'Disable Dispo Screen',
			'disable_dispo_status' => 'Disable Dispo Status',
			'screen_labels' => 'Screen Labels',
			'status_display_fields' => 'Status Display Fields',
			'na_call_url' => 'Na Call Url',
			'survey_recording' => 'Survey Recording',
			'pllb_grouping' => 'Pllb Grouping',
			'pllb_grouping_limit' => 'Pllb Grouping Limit',
			'call_count_limit' => 'Call Count Limit',
			'call_count_target' => 'Call Count Target',
			'callback_hours_block' => 'Callback Hours Block',
			'callback_list_calltime' => 'Callback List Calltime',
			'user_group' => 'User Group',
			'hopper_vlc_dup_check' => 'Hopper Vlc Dup Check',
			'in_group_dial' => 'In Group Dial',
			'in_group_dial_select' => 'In Group Dial Select',
			'safe_harbor_audio_field' => 'Safe Harbor Audio Field',
			'pause_after_next_call' => 'Pause After Next Call',
			'owner_populate' => 'Owner Populate',
			'use_other_campaign_dnc' => 'Use Other Campaign Dnc',
			'allow_emails' => 'Allow Emails',
			'amd_inbound_group' => 'Amd Inbound Group',
			'amd_callmenu' => 'Amd Callmenu',
			'survey_wait_sec' => 'Survey Wait Sec',
			'manual_dial_lead_id' => 'Manual Dial Lead',
			'dead_max' => 'Dead Max',
			'dead_max_dispo' => 'Dead Max Dispo',
			'dispo_max' => 'Dispo Max',
			'dispo_max_dispo' => 'Dispo Max Dispo',
			'pause_max' => 'Pause Max',
			'max_inbound_calls' => 'Max Inbound Calls',
			'manual_dial_search_checkbox' => 'Manual Dial Search Checkbox',
			'hide_call_log_info' => 'Hide Call Log Info',
			'timer_alt_seconds' => 'Timer Alt Seconds',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('campaign_id',$this->campaign_id,true);
		$criteria->compare('campaign_name',$this->campaign_name,true);
		$criteria->compare('active',$this->active,true);
		$criteria->compare('dial_status_a',$this->dial_status_a,true);
		$criteria->compare('dial_status_b',$this->dial_status_b,true);
		$criteria->compare('dial_status_c',$this->dial_status_c,true);
		$criteria->compare('dial_status_d',$this->dial_status_d,true);
		$criteria->compare('dial_status_e',$this->dial_status_e,true);
		$criteria->compare('lead_order',$this->lead_order,true);
		$criteria->compare('park_ext',$this->park_ext,true);
		$criteria->compare('park_file_name',$this->park_file_name,true);
		$criteria->compare('web_form_address',$this->web_form_address,true);
		$criteria->compare('allow_closers',$this->allow_closers,true);
		$criteria->compare('hopper_level',$this->hopper_level,true);
		$criteria->compare('auto_dial_level',$this->auto_dial_level,true);
		$criteria->compare('next_agent_call',$this->next_agent_call,true);
		$criteria->compare('local_call_time',$this->local_call_time,true);
		$criteria->compare('voicemail_ext',$this->voicemail_ext,true);
		$criteria->compare('dial_timeout',$this->dial_timeout);
		$criteria->compare('dial_prefix',$this->dial_prefix,true);
		$criteria->compare('campaign_cid',$this->campaign_cid,true);
		$criteria->compare('campaign_vdad_exten',$this->campaign_vdad_exten,true);
		$criteria->compare('campaign_rec_exten',$this->campaign_rec_exten,true);
		$criteria->compare('campaign_recording',$this->campaign_recording,true);
		$criteria->compare('campaign_rec_filename',$this->campaign_rec_filename,true);
		$criteria->compare('campaign_script',$this->campaign_script,true);
		$criteria->compare('get_call_launch',$this->get_call_launch,true);
		$criteria->compare('am_message_exten',$this->am_message_exten,true);
		$criteria->compare('amd_send_to_vmx',$this->amd_send_to_vmx,true);
		$criteria->compare('xferconf_a_dtmf',$this->xferconf_a_dtmf,true);
		$criteria->compare('xferconf_a_number',$this->xferconf_a_number,true);
		$criteria->compare('xferconf_b_dtmf',$this->xferconf_b_dtmf,true);
		$criteria->compare('xferconf_b_number',$this->xferconf_b_number,true);
		$criteria->compare('alt_number_dialing',$this->alt_number_dialing,true);
		$criteria->compare('scheduled_callbacks',$this->scheduled_callbacks,true);
		$criteria->compare('lead_filter_id',$this->lead_filter_id,true);
		$criteria->compare('drop_call_seconds',$this->drop_call_seconds);
		$criteria->compare('drop_action',$this->drop_action,true);
		$criteria->compare('safe_harbor_exten',$this->safe_harbor_exten,true);
		$criteria->compare('display_dialable_count',$this->display_dialable_count,true);
		$criteria->compare('wrapup_seconds',$this->wrapup_seconds);
		$criteria->compare('wrapup_message',$this->wrapup_message,true);
		$criteria->compare('closer_campaigns',$this->closer_campaigns,true);
		$criteria->compare('use_internal_dnc',$this->use_internal_dnc,true);
		$criteria->compare('allcalls_delay',$this->allcalls_delay);
		$criteria->compare('omit_phone_code',$this->omit_phone_code,true);
		$criteria->compare('dial_method',$this->dial_method,true);
		$criteria->compare('available_only_ratio_tally',$this->available_only_ratio_tally,true);
		$criteria->compare('adaptive_dropped_percentage',$this->adaptive_dropped_percentage,true);
		$criteria->compare('adaptive_maximum_level',$this->adaptive_maximum_level,true);
		$criteria->compare('adaptive_latest_server_time',$this->adaptive_latest_server_time,true);
		$criteria->compare('adaptive_intensity',$this->adaptive_intensity,true);
		$criteria->compare('adaptive_dl_diff_target',$this->adaptive_dl_diff_target);
		$criteria->compare('concurrent_transfers',$this->concurrent_transfers,true);
		$criteria->compare('auto_alt_dial',$this->auto_alt_dial,true);
		$criteria->compare('auto_alt_dial_statuses',$this->auto_alt_dial_statuses,true);
		$criteria->compare('agent_pause_codes_active',$this->agent_pause_codes_active,true);
		$criteria->compare('campaign_description',$this->campaign_description,true);
		$criteria->compare('campaign_changedate',$this->campaign_changedate,true);
		$criteria->compare('campaign_stats_refresh',$this->campaign_stats_refresh,true);
		$criteria->compare('campaign_logindate',$this->campaign_logindate,true);
		$criteria->compare('dial_statuses',$this->dial_statuses,true);
		$criteria->compare('disable_alter_custdata',$this->disable_alter_custdata,true);
		$criteria->compare('no_hopper_leads_logins',$this->no_hopper_leads_logins,true);
		$criteria->compare('list_order_mix',$this->list_order_mix,true);
		$criteria->compare('campaign_allow_inbound',$this->campaign_allow_inbound,true);
		$criteria->compare('manual_dial_list_id',$this->manual_dial_list_id,true);
		$criteria->compare('default_xfer_group',$this->default_xfer_group,true);
		$criteria->compare('xfer_groups',$this->xfer_groups,true);
		$criteria->compare('queue_priority',$this->queue_priority);
		$criteria->compare('drop_inbound_group',$this->drop_inbound_group,true);
		$criteria->compare('qc_enabled',$this->qc_enabled,true);
		$criteria->compare('qc_statuses',$this->qc_statuses,true);
		$criteria->compare('qc_lists',$this->qc_lists,true);
		$criteria->compare('qc_shift_id',$this->qc_shift_id,true);
		$criteria->compare('qc_get_record_launch',$this->qc_get_record_launch,true);
		$criteria->compare('qc_show_recording',$this->qc_show_recording,true);
		$criteria->compare('qc_web_form_address',$this->qc_web_form_address,true);
		$criteria->compare('qc_script',$this->qc_script,true);
		$criteria->compare('survey_first_audio_file',$this->survey_first_audio_file,true);
		$criteria->compare('survey_dtmf_digits',$this->survey_dtmf_digits,true);
		$criteria->compare('survey_ni_digit',$this->survey_ni_digit,true);
		$criteria->compare('survey_opt_in_audio_file',$this->survey_opt_in_audio_file,true);
		$criteria->compare('survey_ni_audio_file',$this->survey_ni_audio_file,true);
		$criteria->compare('survey_method',$this->survey_method,true);
		$criteria->compare('survey_no_response_action',$this->survey_no_response_action,true);
		$criteria->compare('survey_ni_status',$this->survey_ni_status,true);
		$criteria->compare('survey_response_digit_map',$this->survey_response_digit_map,true);
		$criteria->compare('survey_xfer_exten',$this->survey_xfer_exten,true);
		$criteria->compare('survey_camp_record_dir',$this->survey_camp_record_dir,true);
		$criteria->compare('disable_alter_custphone',$this->disable_alter_custphone,true);
		$criteria->compare('display_queue_count',$this->display_queue_count,true);
		$criteria->compare('manual_dial_filter',$this->manual_dial_filter,true);
		$criteria->compare('agent_clipboard_copy',$this->agent_clipboard_copy,true);
		$criteria->compare('agent_extended_alt_dial',$this->agent_extended_alt_dial,true);
		$criteria->compare('use_campaign_dnc',$this->use_campaign_dnc,true);
		$criteria->compare('three_way_call_cid',$this->three_way_call_cid,true);
		$criteria->compare('three_way_dial_prefix',$this->three_way_dial_prefix,true);
		$criteria->compare('web_form_target',$this->web_form_target,true);
		$criteria->compare('vtiger_search_category',$this->vtiger_search_category,true);
		$criteria->compare('vtiger_create_call_record',$this->vtiger_create_call_record,true);
		$criteria->compare('vtiger_create_lead_record',$this->vtiger_create_lead_record,true);
		$criteria->compare('vtiger_screen_login',$this->vtiger_screen_login,true);
		$criteria->compare('cpd_amd_action',$this->cpd_amd_action,true);
		$criteria->compare('agent_allow_group_alias',$this->agent_allow_group_alias,true);
		$criteria->compare('default_group_alias',$this->default_group_alias,true);
		$criteria->compare('vtiger_search_dead',$this->vtiger_search_dead,true);
		$criteria->compare('vtiger_status_call',$this->vtiger_status_call,true);
		$criteria->compare('survey_third_digit',$this->survey_third_digit,true);
		$criteria->compare('survey_third_audio_file',$this->survey_third_audio_file,true);
		$criteria->compare('survey_third_status',$this->survey_third_status,true);
		$criteria->compare('survey_third_exten',$this->survey_third_exten,true);
		$criteria->compare('survey_fourth_digit',$this->survey_fourth_digit,true);
		$criteria->compare('survey_fourth_audio_file',$this->survey_fourth_audio_file,true);
		$criteria->compare('survey_fourth_status',$this->survey_fourth_status,true);
		$criteria->compare('survey_fourth_exten',$this->survey_fourth_exten,true);
		$criteria->compare('drop_lockout_time',$this->drop_lockout_time,true);
		$criteria->compare('quick_transfer_button',$this->quick_transfer_button,true);
		$criteria->compare('prepopulate_transfer_preset',$this->prepopulate_transfer_preset,true);
		$criteria->compare('drop_rate_group',$this->drop_rate_group,true);
		$criteria->compare('view_calls_in_queue',$this->view_calls_in_queue,true);
		$criteria->compare('view_calls_in_queue_launch',$this->view_calls_in_queue_launch,true);
		$criteria->compare('grab_calls_in_queue',$this->grab_calls_in_queue,true);
		$criteria->compare('call_requeue_button',$this->call_requeue_button,true);
		$criteria->compare('pause_after_each_call',$this->pause_after_each_call,true);
		$criteria->compare('no_hopper_dialing',$this->no_hopper_dialing,true);
		$criteria->compare('agent_dial_owner_only',$this->agent_dial_owner_only,true);
		$criteria->compare('agent_display_dialable_leads',$this->agent_display_dialable_leads,true);
		$criteria->compare('web_form_address_two',$this->web_form_address_two,true);
		$criteria->compare('waitforsilence_options',$this->waitforsilence_options,true);
		$criteria->compare('agent_select_territories',$this->agent_select_territories,true);
		$criteria->compare('campaign_calldate',$this->campaign_calldate,true);
		$criteria->compare('crm_popup_login',$this->crm_popup_login,true);
		$criteria->compare('crm_login_address',$this->crm_login_address,true);
		$criteria->compare('timer_action',$this->timer_action,true);
		$criteria->compare('timer_action_message',$this->timer_action_message,true);
		$criteria->compare('timer_action_seconds',$this->timer_action_seconds);
		$criteria->compare('start_call_url',$this->start_call_url,true);
		$criteria->compare('dispo_call_url',$this->dispo_call_url,true);
		$criteria->compare('xferconf_c_number',$this->xferconf_c_number,true);
		$criteria->compare('xferconf_d_number',$this->xferconf_d_number,true);
		$criteria->compare('xferconf_e_number',$this->xferconf_e_number,true);
		$criteria->compare('use_custom_cid',$this->use_custom_cid,true);
		$criteria->compare('scheduled_callbacks_alert',$this->scheduled_callbacks_alert,true);
		$criteria->compare('queuemetrics_callstatus_override',$this->queuemetrics_callstatus_override,true);
		$criteria->compare('extension_appended_cidname',$this->extension_appended_cidname,true);
		$criteria->compare('scheduled_callbacks_count',$this->scheduled_callbacks_count,true);
		$criteria->compare('manual_dial_override',$this->manual_dial_override,true);
		$criteria->compare('blind_monitor_warning',$this->blind_monitor_warning,true);
		$criteria->compare('blind_monitor_message',$this->blind_monitor_message,true);
		$criteria->compare('blind_monitor_filename',$this->blind_monitor_filename,true);
		$criteria->compare('inbound_queue_no_dial',$this->inbound_queue_no_dial,true);
		$criteria->compare('timer_action_destination',$this->timer_action_destination,true);
		$criteria->compare('enable_xfer_presets',$this->enable_xfer_presets,true);
		$criteria->compare('hide_xfer_number_to_dial',$this->hide_xfer_number_to_dial,true);
		$criteria->compare('manual_dial_prefix',$this->manual_dial_prefix,true);
		$criteria->compare('customer_3way_hangup_logging',$this->customer_3way_hangup_logging,true);
		$criteria->compare('customer_3way_hangup_seconds',$this->customer_3way_hangup_seconds);
		$criteria->compare('customer_3way_hangup_action',$this->customer_3way_hangup_action,true);
		$criteria->compare('ivr_park_call',$this->ivr_park_call,true);
		$criteria->compare('ivr_park_call_agi',$this->ivr_park_call_agi,true);
		$criteria->compare('manual_preview_dial',$this->manual_preview_dial,true);
		$criteria->compare('realtime_agent_time_stats',$this->realtime_agent_time_stats,true);
		$criteria->compare('use_auto_hopper',$this->use_auto_hopper,true);
		$criteria->compare('auto_hopper_multi',$this->auto_hopper_multi,true);
		$criteria->compare('auto_hopper_level',$this->auto_hopper_level);
		$criteria->compare('auto_trim_hopper',$this->auto_trim_hopper,true);
		$criteria->compare('api_manual_dial',$this->api_manual_dial,true);
		$criteria->compare('manual_dial_call_time_check',$this->manual_dial_call_time_check,true);
		$criteria->compare('display_leads_count',$this->display_leads_count,true);
		$criteria->compare('lead_order_randomize',$this->lead_order_randomize,true);
		$criteria->compare('lead_order_secondary',$this->lead_order_secondary,true);
		$criteria->compare('per_call_notes',$this->per_call_notes,true);
		$criteria->compare('my_callback_option',$this->my_callback_option,true);
		$criteria->compare('agent_lead_search',$this->agent_lead_search,true);
		$criteria->compare('agent_lead_search_method',$this->agent_lead_search_method,true);
		$criteria->compare('queuemetrics_phone_environment',$this->queuemetrics_phone_environment,true);
		$criteria->compare('auto_pause_precall',$this->auto_pause_precall,true);
		$criteria->compare('auto_pause_precall_code',$this->auto_pause_precall_code,true);
		$criteria->compare('auto_resume_precall',$this->auto_resume_precall,true);
		$criteria->compare('manual_dial_cid',$this->manual_dial_cid,true);
		$criteria->compare('post_phone_time_diff_alert',$this->post_phone_time_diff_alert,true);
		$criteria->compare('custom_3way_button_transfer',$this->custom_3way_button_transfer,true);
		$criteria->compare('available_only_tally_threshold',$this->available_only_tally_threshold,true);
		$criteria->compare('available_only_tally_threshold_agents',$this->available_only_tally_threshold_agents);
		$criteria->compare('dial_level_threshold',$this->dial_level_threshold,true);
		$criteria->compare('dial_level_threshold_agents',$this->dial_level_threshold_agents);
		$criteria->compare('safe_harbor_audio',$this->safe_harbor_audio,true);
		$criteria->compare('safe_harbor_menu_id',$this->safe_harbor_menu_id,true);
		$criteria->compare('survey_menu_id',$this->survey_menu_id,true);
		$criteria->compare('callback_days_limit',$this->callback_days_limit);
		$criteria->compare('dl_diff_target_method',$this->dl_diff_target_method,true);
		$criteria->compare('disable_dispo_screen',$this->disable_dispo_screen,true);
		$criteria->compare('disable_dispo_status',$this->disable_dispo_status,true);
		$criteria->compare('screen_labels',$this->screen_labels,true);
		$criteria->compare('status_display_fields',$this->status_display_fields,true);
		$criteria->compare('na_call_url',$this->na_call_url,true);
		$criteria->compare('survey_recording',$this->survey_recording,true);
		$criteria->compare('pllb_grouping',$this->pllb_grouping,true);
		$criteria->compare('pllb_grouping_limit',$this->pllb_grouping_limit);
		$criteria->compare('call_count_limit',$this->call_count_limit);
		$criteria->compare('call_count_target',$this->call_count_target);
		$criteria->compare('callback_hours_block',$this->callback_hours_block);
		$criteria->compare('callback_list_calltime',$this->callback_list_calltime,true);
		$criteria->compare('user_group',$this->user_group,true);
		$criteria->compare('hopper_vlc_dup_check',$this->hopper_vlc_dup_check,true);
		$criteria->compare('in_group_dial',$this->in_group_dial,true);
		$criteria->compare('in_group_dial_select',$this->in_group_dial_select,true);
		$criteria->compare('safe_harbor_audio_field',$this->safe_harbor_audio_field,true);
		$criteria->compare('pause_after_next_call',$this->pause_after_next_call,true);
		$criteria->compare('owner_populate',$this->owner_populate,true);
		$criteria->compare('use_other_campaign_dnc',$this->use_other_campaign_dnc,true);
		$criteria->compare('allow_emails',$this->allow_emails,true);
		$criteria->compare('amd_inbound_group',$this->amd_inbound_group,true);
		$criteria->compare('amd_callmenu',$this->amd_callmenu,true);
		$criteria->compare('survey_wait_sec',$this->survey_wait_sec);
		$criteria->compare('manual_dial_lead_id',$this->manual_dial_lead_id,true);
		$criteria->compare('dead_max',$this->dead_max);
		$criteria->compare('dead_max_dispo',$this->dead_max_dispo,true);
		$criteria->compare('dispo_max',$this->dispo_max);
		$criteria->compare('dispo_max_dispo',$this->dispo_max_dispo,true);
		$criteria->compare('pause_max',$this->pause_max);
		$criteria->compare('max_inbound_calls',$this->max_inbound_calls);
		$criteria->compare('manual_dial_search_checkbox',$this->manual_dial_search_checkbox,true);
		$criteria->compare('hide_call_log_info',$this->hide_call_log_info,true);
		$criteria->compare('timer_alt_seconds',$this->timer_alt_seconds);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return VicidialCampaigns the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
