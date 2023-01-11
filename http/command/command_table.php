<?php
//-----------------------------------------------------------------------------------------------------------
// Login Command
//-----------------------------------------------------------------------------------------------------------
define('Login', 'login');
define('Logout', 'logout');

//-----------------------------------------------------------------------------------------------------------
// building Command
//-----------------------------------------------------------------------------------------------------------
define('BuildingManager', 'building_manager');
define('BuildingList', 'building_list');
define('MobileFloorInfo', 'm_floor_info');

//-----------------------------------------------------------------------------------------------------------
// Frame Command
//-----------------------------------------------------------------------------------------------------------
define('MobileFrame', 'm_frame');

//-----------------------------------------------------------------------------------------------------------
// Home Command
//-----------------------------------------------------------------------------------------------------------
define('MobileHome', 'm_home');

//-----------------------------------------------------------------------------------------------------------
// Report Command
//-----------------------------------------------------------------------------------------------------------
define('ReportEnergy', 'report_energy');
define('ReportFloor', 'report_floor');
define('ReportPeriod', 'report_period');

//-----------------------------------------------------------------------------------------------------------
// Analysis Command
//-----------------------------------------------------------------------------------------------------------
define('AnalysisGroupInfo', 'analysis_group_info');
define('AnalysisZero', 'analysis_zero');
define('AnalysisZeroTest', 'analysis_zero_test');
define('AnalysisEnergy', 'analysis_energy');
define('AnalysisPeriod', 'analysis_period');
define('AnalysisFloor', 'analysis_floor');
define('AnalysisTotal', 'analysis_total');

//-----------------------------------------------------------------------------------------------------------
// Watchdog Command
//-----------------------------------------------------------------------------------------------------------
// 통계
define('ArrangeTime', 'arrange_time');
define('ArrangeDay', 'arrange_day');
define('ArrangeMonth', 'arrange_month');
define('ArrangeFinedustDay', 'arrange_finedust_day');
define('ArrangeFinedustMonth', 'arrange_finedust_month');
define('ArrangeCo2Day', 'arrange_co2_day');
define('ArrangeCo2Month', 'arrange_co2_month');
define('ArrangeEfficiencyTime', 'arrange_efficiency_time');
define('ArrangeEfficiencyDay', 'arrange_efficiency_day');
define('ArrangeEfficiencyMonth', 'arrange_efficiency_month');
define('ArrangeStatusTypeDay', 'arrange_status_type_day');
define('ArrangeStatusTypeMonth', 'arrange_status_type_month');

// 데이터 수신
define('AddElectricMdmt', 'add_electric_mdmt');
define('AddElectricMeterNtek', 'add_electric_meter_ntek');
define('ArrangeAiPrediction', 'arrange_ai_prediction');
define('AddFinedust', 'add_finedust');
define('AddMeterCnc', 'add_meter_cnc');

// TOC로 전달
define('AddMeterTOC', 'add_meter_toc');
define('AddComplexDataTOC', 'add_complex_data_toc');

// 계산식
define('AddElechotMdmt', 'add_elechot_mdmt');
define('AddEleventMdmt', 'add_elevent_mdmt');
define('AddElectricAllMdmt', 'add_electric_all_mdmt');
define('AddCommunicationPowerMdmt', 'add_communication_power_mdmt');
define('AddElechotTbmt', 'add_elechot_tbmt');
define('AddElectricAllTbmt', 'add_electric_all_tbmt');
define('AddElectricAllNedOb', 'add_electric_all_nedOb');
define('AddElectricAllScnr', 'add_electric_all_scnr');
define('AddElectricAllBangbae', 'add_electric_all_bangbae');
define('AddElectricAllDado', 'add_electric_all_dado');
define('AddElectricAllKhc', 'add_electric_all_khc');
define('AddElectricAllHjecc', 'add_electric_all_hjecc');
define('AddElectricAllSct', 'add_electric_all_sct');
define('AddElectricAllBhmt', 'add_electric_all_bhmt');
define('AddElectricAllKsbc', 'add_electric_all_ksbc');
define('AddElectricAllKfl', 'add_electric_all_kfl');

//-----------------------------------------------------------------------------------------------------------
// Dashboard Command
//-----------------------------------------------------------------------------------------------------------
define('DashboardReferenceSave', 'dashboard_reference_save');
define('Dashboard', 'dashboard');
define('DashboardFloor', 'dashboard_floor');
define('DashboardFloorFacility', 'dashboard_floor_facility');

//-----------------------------------------------------------------------------------------------------------
// Weather Command
//-----------------------------------------------------------------------------------------------------------
define('WeatherFinedust', 'weather_finedust');
define('WeatherOpenApi', 'weather_open_api');
define('WeatherSunRiseSet', 'weather_sun_riseset');
define('WeatherTempHumiCur', 'weather_temp_humi_cur');
define('WeatherMinistryFinedust', 'weather_ministry_finedust');

//-----------------------------------------------------------------------------------------------------------
// Control Command
//-----------------------------------------------------------------------------------------------------------
define('Control', 'control');
define('ControlSet', 'control_set');

//-----------------------------------------------------------------------------------------------------------
// Prediction Command
//-----------------------------------------------------------------------------------------------------------
define('EnergyPrediction', 'energy_prediction');
define('SolarPrediction', 'solar_prediction');
define('MobilePrediction', 'm_prediction');

//-----------------------------------------------------------------------------------------------------------
// Alarm Command
//-----------------------------------------------------------------------------------------------------------
define('MonitorAlarmOn', 'monitor_alarm_on');
define('MonitorAlarmOff', 'monitor_alarm_off');
	
//-----------------------------------------------------------------------------------------------------------
// Facility Command
//-----------------------------------------------------------------------------------------------------------
define('Facility', 'facility');
define('FacilityEfficiency', 'facility_efficiency');

//-----------------------------------------------------------------------------------------------------------
// Hindrance Alarm Command
//-----------------------------------------------------------------------------------------------------------
define('HindranceAlarm', 'hindrance_alarm');
define('HindranceStatus', 'hindrance_status');
define('HindranceExcel', 'hindrance_excel');
define('HindranceInfo', 'hindrance_info');

//-----------------------------------------------------------------------------------------------------------
// Info Command
//-----------------------------------------------------------------------------------------------------------
define('InfoFinedust', 'info_finedust');
define('InfoPopup', 'info_popup');

define('InfoEnergy', 'info_energy');
define('InfoUsage', 'info_usage');
define('InfoFacilities', 'info_facilities');
define('InfoEnvironment', 'info_environment');
define('InfoStatus', 'info_status');

//-----------------------------------------------------------------------------------------------------------
// Diagram Command
//-----------------------------------------------------------------------------------------------------------
define('Diagram', 'diagram');
define('DiagramFacility', 'diagram_facility');
define('DiagramKey', 'diagram_key');
define('MobileDiagram', 'm_diagram');

//-----------------------------------------------------------------------------------------------------------
// Set Command
//-----------------------------------------------------------------------------------------------------------
define('SetInfo', 'set_info');
define('SetSave', 'set_save');
define('SetStandard', 'set_standard');
define('SetStandardSave', 'set_standard_save');
define('SetStandardCode', 'set_standard_code');
define('UnitPrice', 'unit_price');
define('SetUnitPrice', 'set_unit_price');
define('UnitPriceKepco', 'unit_price_kepco');
define('UnitPriceKepcoInfo', 'unit_price_kepco_info');
define('UnitPriceKepcoSet', 'unit_price_kepco_set');
define('Instrument', 'instrument');
define('MonitoringInfo', 'monitoring_info');

//-----------------------------------------------------------------------------------------------------------
// Manager Command
//-----------------------------------------------------------------------------------------------------------
define('ManagerBuilding', 'manager_building');
define('ManagerLogin', 'manager_login');
define('ManagerAuthority', 'manager_authority');
define('Equipment', 'equipment');
define('EquipmentSet', 'equipment_set');
define('EquipmentInfo', 'equipment_info');
define('PasswordInitialize', 'password_initialize');

//-----------------------------------------------------------------------------------------------------------
// Solar Command
//-----------------------------------------------------------------------------------------------------------
define('Solar', 'solar');
define('SolarExcel', 'solar_excel');

//-----------------------------------------------------------------------------------------------------------
// Menu Command
//-----------------------------------------------------------------------------------------------------------
define('EnergyButton', 'energy_button');
define('MenuLocation', 'menu_location');
define('MenuAuthority', 'menu_authority');

//-----------------------------------------------------------------------------------------------------------
// Paper Command
//-----------------------------------------------------------------------------------------------------------
define('Paper', 'paper');
define('PaperExcel', 'paper_excel');

//-----------------------------------------------------------------------------------------------------------
// Account Command
//-----------------------------------------------------------------------------------------------------------
define('ChangePassword', 'change_password');
define('MakeApiAccountKey', 'make_api_account_key');

//-----------------------------------------------------------------------------------------------------------
// Auth Command
//-----------------------------------------------------------------------------------------------------------
define('ReceiveAuthNum', 'receive_auth_num');
define('ConfirmAuthInfo', 'confirm_auth_info');

//-----------------------------------------------------------------------------------------------------------
// Migration Command
//-----------------------------------------------------------------------------------------------------------
define('MigrationDailyTableUsed', 'migration_daily_table_used');
define('MigrationMonthTableUsed', 'migration_month_table_used');
define('MigrationWeather', 'migration_weather');
define('MigrationLoginIpEncryption', 'migration_login_ip_encryption');
define('MigrationMeterNtek', 'migration_meter_ntek');
define('MigrationMonthTableEfficiency', 'migration_month_table_efficiency');
define('MigrationDailyTableStatus', 'migration_daily_table_status');
define('MigrationMonthTableStatus', 'migration_month_table_status');
define('MigrationAdminEncryptionIV', 'migration_admin_encryption_iv');
define('MigrationComplexEncryptionIV', 'migration_complex_encryption_iv');
define('MigrationMeterCnc', 'migration_meter_cnc');
define('MigrationMeterCopy', 'migration_meter_copy');

//-----------------------------------------------------------------------------------------------------------
// Test Command
//-----------------------------------------------------------------------------------------------------------
define('DashboardTest', 'dashboard_test');
define('MailerTest', 'mailer_test');
define('CacheRawDataTest', 'cache_raw_data_test');
define('SMSGabiaTest', 'sms_gabia_test');
define('ElectricPriceTest', 'electric_price_test');
define('DailyElectricLibraryPrice', 'daily_electric_library_price');
define('AreaUsedTest', 'area_used_test');
define('EncryptionTest', 'encryption_test');
define('SecretKeyTest', 'secret_key_test');
define('EfficiencyTest', 'efficiency_test');
define('ApiTest', 'api_test');

//-----------------------------------------------------------------------------------------------------------
// Cache Command
//-----------------------------------------------------------------------------------------------------------
define('CacheKepcoInfo', 'cache_kepco_info');

//-----------------------------------------------------------------------------------------------------------
// Common Command
//-----------------------------------------------------------------------------------------------------------
define('FileDownload', 'file_download');
define('EnergyCode', 'energy_code');
define('HomeInfo', 'home_info');
define('TmpSession', 'tmp_session');

//-----------------------------------------------------------------------------------------------------------
// Calendar Command
//-----------------------------------------------------------------------------------------------------------
define('HolidayApi', 'holiday_api');

//-----------------------------------------------------------------------------------------------------------
// Integration Command
//-----------------------------------------------------------------------------------------------------------
define('IntegrationComplexSend', 'integration_complex_send');
define('IntegrationElectricSend', 'integration_electric_send');
define('IntegrationGasSend', 'integration_gas_send');
define('IntegrationSolarSend', 'integration_solar_send');
define('IntegrationFindedustSend', 'integration_finedust_send');