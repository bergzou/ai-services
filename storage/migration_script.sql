/*--------------------------------------------------*/
/* Migration SQL for table: infra_api_access_log */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`infra_api_access_log` (
    `id`,
    `snowflake_id`,
    `trace_id`,
    `user_id`,
    `user_type`,
    `application_name`,
    `request_method`,
    `request_url`,
    `request_params`,
    `response_body`,
    `user_ip`,
    `user_agent`,
    `operate_module`,
    `operate_name`,
    `operate_type`,
    `begin_time`,
    `end_time`,
    `duration`,
    `result_code`,
    `result_msg`,
    `tenant_id`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `trace_id` AS `trace_id`,
    `user_id` AS `user_id`,
    `user_type` AS `user_type`,
    `application_name` AS `application_name`,
    `request_method` AS `request_method`,
    `request_url` AS `request_url`,
    `request_params` AS `request_params`,
    `response_body` AS `response_body`,
    `user_ip` AS `user_ip`,
    `user_agent` AS `user_agent`,
    `operate_module` AS `operate_module`,
    `operate_name` AS `operate_name`,
    `operate_type` AS `operate_type`,
    `begin_time` AS `begin_time`,
    `end_time` AS `end_time`,
    `duration` AS `duration`,
    `result_code` AS `result_code`,
    `result_msg` AS `result_msg`,
    `tenant_id` AS `tenant_id`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`infra_api_access_log`;

/*--------------------------------------------------*/
/* Migration SQL for table: infra_api_error_log */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`infra_api_error_log` (
    `id`,
    `snowflake_id`,
    `trace_id`,
    `user_id`,
    `user_type`,
    `application_name`,
    `request_method`,
    `request_url`,
    `request_params`,
    `user_ip`,
    `user_agent`,
    `exception_time`,
    `exception_name`,
    `exception_message`,
    `exception_root_cause_message`,
    `exception_stack_trace`,
    `exception_class_name`,
    `exception_file_name`,
    `exception_method_name`,
    `exception_line_number`,
    `process_status`,
    `process_time`,
    `process_user_id`,
    `tenant_id`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `trace_id` AS `trace_id`,
    `user_id` AS `user_id`,
    `user_type` AS `user_type`,
    `application_name` AS `application_name`,
    `request_method` AS `request_method`,
    `request_url` AS `request_url`,
    `request_params` AS `request_params`,
    `user_ip` AS `user_ip`,
    `user_agent` AS `user_agent`,
    `exception_time` AS `exception_time`,
    `exception_name` AS `exception_name`,
    `exception_message` AS `exception_message`,
    `exception_root_cause_message` AS `exception_root_cause_message`,
    `exception_stack_trace` AS `exception_stack_trace`,
    `exception_class_name` AS `exception_class_name`,
    `exception_file_name` AS `exception_file_name`,
    `exception_method_name` AS `exception_method_name`,
    `exception_line_number` AS `exception_line_number`,
    `process_status` AS `process_status`,
    `process_time` AS `process_time`,
    `process_user_id` AS `process_user_id`,
    `tenant_id` AS `tenant_id`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`infra_api_error_log`;

/*--------------------------------------------------*/
/* Migration SQL for table: infra_codegen_column */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`infra_codegen_column` (
    `id`,
    `snowflake_id`,
    `table_id`,
    `column_name`,
    `data_type`,
    `column_comment`,
    `nullable`,
    `primary_key`,
    `ordinal_position`,
    `java_type`,
    `java_field`,
    `dict_type`,
    `example`,
    `create_operation`,
    `update_operation`,
    `list_operation`,
    `list_operation_condition`,
    `list_operation_result`,
    `html_type`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `table_id` AS `table_id`,
    `column_name` AS `column_name`,
    `data_type` AS `data_type`,
    `column_comment` AS `column_comment`,
    `nullable` AS `nullable`,
    `primary_key` AS `primary_key`,
    `ordinal_position` AS `ordinal_position`,
    `java_type` AS `java_type`,
    `java_field` AS `java_field`,
    `dict_type` AS `dict_type`,
    `example` AS `example`,
    `create_operation` AS `create_operation`,
    `update_operation` AS `update_operation`,
    `list_operation` AS `list_operation`,
    `list_operation_condition` AS `list_operation_condition`,
    `list_operation_result` AS `list_operation_result`,
    `html_type` AS `html_type`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`infra_codegen_column`;

/*--------------------------------------------------*/
/* Migration SQL for table: infra_codegen_table */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`infra_codegen_table` (
    `id`,
    `snowflake_id`,
    `data_source_config_id`,
    `scene`,
    `table_name`,
    `table_comment`,
    `remark`,
    `module_name`,
    `business_name`,
    `class_name`,
    `class_comment`,
    `author`,
    `template_type`,
    `front_type`,
    `parent_menu_id`,
    `master_table_id`,
    `sub_join_column_id`,
    `sub_join_many`,
    `tree_parent_column_id`,
    `tree_name_column_id`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `data_source_config_id` AS `data_source_config_id`,
    `scene` AS `scene`,
    `table_name` AS `table_name`,
    `table_comment` AS `table_comment`,
    `remark` AS `remark`,
    `module_name` AS `module_name`,
    `business_name` AS `business_name`,
    `class_name` AS `class_name`,
    `class_comment` AS `class_comment`,
    `author` AS `author`,
    `template_type` AS `template_type`,
    `front_type` AS `front_type`,
    `parent_menu_id` AS `parent_menu_id`,
    `master_table_id` AS `master_table_id`,
    `sub_join_column_id` AS `sub_join_column_id`,
    `sub_join_many` AS `sub_join_many`,
    `tree_parent_column_id` AS `tree_parent_column_id`,
    `tree_name_column_id` AS `tree_name_column_id`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`infra_codegen_table`;

/*--------------------------------------------------*/
/* Migration SQL for table: infra_config */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`infra_config` (
    `id`,
    `snowflake_id`,
    `category`,
    `type`,
    `name`,
    `config_key`,
    `value`,
    `visible`,
    `remark`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `category` AS `category`,
    `type` AS `type`,
    `name` AS `name`,
    `config_key` AS `config_key`,
    `value` AS `value`,
    `visible` AS `visible`,
    `remark` AS `remark`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`infra_config`;

/*--------------------------------------------------*/
/* Migration SQL for table: infra_data_source_config */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`infra_data_source_config` (
    `id`,
    `snowflake_id`,
    `name`,
    `url`,
    `username`,
    `password`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `name` AS `name`,
    `url` AS `url`,
    `username` AS `username`,
    `password` AS `password`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`infra_data_source_config`;

/*--------------------------------------------------*/
/* Migration SQL for table: infra_file */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`infra_file` (
    `id`,
    `snowflake_id`,
    `config_id`,
    `name`,
    `path`,
    `url`,
    `type`,
    `size`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `config_id` AS `config_id`,
    `name` AS `name`,
    `path` AS `path`,
    `url` AS `url`,
    `type` AS `type`,
    `size` AS `size`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`infra_file`;

/*--------------------------------------------------*/
/* Migration SQL for table: infra_file_config */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`infra_file_config` (
    `id`,
    `snowflake_id`,
    `name`,
    `storage`,
    `remark`,
    `master`,
    `config`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `name` AS `name`,
    `storage` AS `storage`,
    `remark` AS `remark`,
    `master` AS `master`,
    `config` AS `config`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`infra_file_config`;

/*--------------------------------------------------*/
/* Migration SQL for table: infra_file_content */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`infra_file_content` (
    `id`,
    `snowflake_id`,
    `config_id`,
    `path`,
    `content`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `config_id` AS `config_id`,
    `path` AS `path`,
    `content` AS `content`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`infra_file_content`;

/*--------------------------------------------------*/
/* Migration SQL for table: infra_job */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`infra_job` (
    `id`,
    `snowflake_id`,
    `name`,
    `status`,
    `handler_name`,
    `handler_param`,
    `cron_expression`,
    `retry_count`,
    `retry_interval`,
    `monitor_timeout`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `name` AS `name`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `handler_name` AS `handler_name`,
    `handler_param` AS `handler_param`,
    `cron_expression` AS `cron_expression`,
    `retry_count` AS `retry_count`,
    `retry_interval` AS `retry_interval`,
    `monitor_timeout` AS `monitor_timeout`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`infra_job`;

/*--------------------------------------------------*/
/* Migration SQL for table: infra_job_log */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`infra_job_log` (
    `id`,
    `snowflake_id`,
    `job_id`,
    `handler_name`,
    `handler_param`,
    `execute_index`,
    `begin_time`,
    `end_time`,
    `duration`,
    `status`,
    `result`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `job_id` AS `job_id`,
    `handler_name` AS `handler_name`,
    `handler_param` AS `handler_param`,
    `execute_index` AS `execute_index`,
    `begin_time` AS `begin_time`,
    `end_time` AS `end_time`,
    `duration` AS `duration`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `result` AS `result`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`infra_job_log`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_dept */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_dept` (
    `id`,
    `snowflake_id`,
    `name`,
    `parent_id`,
    `sort`,
    `leader_user_id`,
    `phone`,
    `email`,
    `status`,
    `tenant_id`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `name` AS `name`,
    `parent_id` AS `parent_id`,
    `sort` AS `sort`,
    `leader_user_id` AS `leader_user_id`,
    `phone` AS `phone`,
    `email` AS `email`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `tenant_id` AS `tenant_id`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_dept`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_dict_data */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_dict_data` (
    `id`,
    `snowflake_id`,
    `sort`,
    `label`,
    `value`,
    `dict_type`,
    `status`,
    `color_type`,
    `css_class`,
    `remark`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `sort` AS `sort`,
    `label` AS `label`,
    `value` AS `value`,
    `dict_type` AS `dict_type`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `color_type` AS `color_type`,
    `css_class` AS `css_class`,
    `remark` AS `remark`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_dict_data`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_dict_type */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_dict_type` (
    `id`,
    `snowflake_id`,
    `name`,
    `type`,
    `status`,
    `remark`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `name` AS `name`,
    `type` AS `type`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `remark` AS `remark`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_dict_type`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_login_log */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_login_log` (
    `id`,
    `snowflake_id`,
    `log_type`,
    `trace_id`,
    `user_id`,
    `user_type`,
    `username`,
    `result`,
    `user_ip`,
    `user_agent`,
    `tenant_id`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `log_type` AS `log_type`,
    `trace_id` AS `trace_id`,
    `user_id` AS `user_id`,
    `user_type` AS `user_type`,
    `username` AS `username`,
    `result` AS `result`,
    `user_ip` AS `user_ip`,
    `user_agent` AS `user_agent`,
    `tenant_id` AS `tenant_id`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_login_log`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_mail_account */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_mail_account` (
    `id`,
    `snowflake_id`,
    `mail`,
    `username`,
    `password`,
    `host`,
    `port`,
    `ssl_enable`,
    `starttls_enable`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `mail` AS `mail`,
    `username` AS `username`,
    `password` AS `password`,
    `host` AS `host`,
    `port` AS `port`,
    `ssl_enable` AS `ssl_enable`,
    `starttls_enable` AS `starttls_enable`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_mail_account`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_mail_log */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_mail_log` (
    `id`,
    `snowflake_id`,
    `user_id`,
    `user_type`,
    `to_mail`,
    `account_id`,
    `from_mail`,
    `template_id`,
    `template_code`,
    `template_nickname`,
    `template_title`,
    `template_content`,
    `template_params`,
    `send_status`,
    `send_time`,
    `send_message_id`,
    `send_exception`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `user_id` AS `user_id`,
    `user_type` AS `user_type`,
    `to_mail` AS `to_mail`,
    `account_id` AS `account_id`,
    `from_mail` AS `from_mail`,
    `template_id` AS `template_id`,
    `template_code` AS `template_code`,
    `template_nickname` AS `template_nickname`,
    `template_title` AS `template_title`,
    `template_content` AS `template_content`,
    `template_params` AS `template_params`,
    `send_status` AS `send_status`,
    `send_time` AS `send_time`,
    `send_message_id` AS `send_message_id`,
    `send_exception` AS `send_exception`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_mail_log`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_mail_template */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_mail_template` (
    `id`,
    `snowflake_id`,
    `name`,
    `code`,
    `account_id`,
    `nickname`,
    `title`,
    `content`,
    `params`,
    `status`,
    `remark`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `name` AS `name`,
    `code` AS `code`,
    `account_id` AS `account_id`,
    `nickname` AS `nickname`,
    `title` AS `title`,
    `content` AS `content`,
    `params` AS `params`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `remark` AS `remark`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_mail_template`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_menu */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_menu` (
    `id`,
    `snowflake_id`,
    `name`,
    `permission`,
    `type`,
    `sort`,
    `parent_id`,
    `path`,
    `icon`,
    `component`,
    `component_name`,
    `status`,
    `visible`,
    `keep_alive`,
    `always_show`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `name` AS `name`,
    `permission` AS `permission`,
    `type` AS `type`,
    `sort` AS `sort`,
    `parent_id` AS `parent_id`,
    `path` AS `path`,
    `icon` AS `icon`,
    `component` AS `component`,
    `component_name` AS `component_name`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `visible` AS `visible`,
    `keep_alive` AS `keep_alive`,
    `always_show` AS `always_show`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_menu`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_notice */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_notice` (
    `id`,
    `snowflake_id`,
    `title`,
    `content`,
    `type`,
    `status`,
    `tenant_id`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `title` AS `title`,
    `content` AS `content`,
    `type` AS `type`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `tenant_id` AS `tenant_id`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_notice`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_notify_message */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_notify_message` (
    `id`,
    `snowflake_id`,
    `user_id`,
    `user_type`,
    `template_id`,
    `template_code`,
    `template_nickname`,
    `template_content`,
    `template_type`,
    `template_params`,
    `read_status`,
    `read_time`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `tenant_id`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `user_id` AS `user_id`,
    `user_type` AS `user_type`,
    `template_id` AS `template_id`,
    `template_code` AS `template_code`,
    `template_nickname` AS `template_nickname`,
    `template_content` AS `template_content`,
    `template_type` AS `template_type`,
    `template_params` AS `template_params`,
    `read_status` AS `read_status`,
    `read_time` AS `read_time`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    `tenant_id` AS `tenant_id`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_notify_message`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_notify_template */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_notify_template` (
    `id`,
    `snowflake_id`,
    `name`,
    `code`,
    `nickname`,
    `content`,
    `type`,
    `params`,
    `status`,
    `remark`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `tenant_id`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `name` AS `name`,
    `code` AS `code`,
    `nickname` AS `nickname`,
    `content` AS `content`,
    `type` AS `type`,
    `params` AS `params`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `remark` AS `remark`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    NULL AS `tenant_id`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_notify_template`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_oauth2_access_token */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_oauth2_access_token` (
    `id`,
    `snowflake_id`,
    `user_id`,
    `user_type`,
    `user_info`,
    `access_token`,
    `refresh_token`,
    `client_id`,
    `scopes`,
    `expires_time`,
    `tenant_id`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `user_id` AS `user_id`,
    `user_type` AS `user_type`,
    `user_info` AS `user_info`,
    `access_token` AS `access_token`,
    `refresh_token` AS `refresh_token`,
    `client_id` AS `client_id`,
    `scopes` AS `scopes`,
    `expires_time` AS `expires_time`,
    `tenant_id` AS `tenant_id`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_oauth2_access_token`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_oauth2_approve */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_oauth2_approve` (
    `id`,
    `snowflake_id`,
    `user_id`,
    `user_type`,
    `client_id`,
    `scope`,
    `approved`,
    `expires_time`,
    `tenant_id`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `user_id` AS `user_id`,
    `user_type` AS `user_type`,
    `client_id` AS `client_id`,
    `scope` AS `scope`,
    `approved` AS `approved`,
    `expires_time` AS `expires_time`,
    `tenant_id` AS `tenant_id`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_oauth2_approve`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_oauth2_client */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_oauth2_client` (
    `id`,
    `snowflake_id`,
    `client_id`,
    `secret`,
    `name`,
    `logo`,
    `description`,
    `status`,
    `access_token_validity_seconds`,
    `refresh_token_validity_seconds`,
    `redirect_uris`,
    `authorized_grant_types`,
    `scopes`,
    `auto_approve_scopes`,
    `authorities`,
    `resource_ids`,
    `additional_information`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `client_id` AS `client_id`,
    `secret` AS `secret`,
    `name` AS `name`,
    `logo` AS `logo`,
    `description` AS `description`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `access_token_validity_seconds` AS `access_token_validity_seconds`,
    `refresh_token_validity_seconds` AS `refresh_token_validity_seconds`,
    `redirect_uris` AS `redirect_uris`,
    `authorized_grant_types` AS `authorized_grant_types`,
    `scopes` AS `scopes`,
    `auto_approve_scopes` AS `auto_approve_scopes`,
    `authorities` AS `authorities`,
    `resource_ids` AS `resource_ids`,
    `additional_information` AS `additional_information`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_oauth2_client`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_oauth2_code */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_oauth2_code` (
    `id`,
    `snowflake_id`,
    `user_id`,
    `user_type`,
    `code`,
    `client_id`,
    `scopes`,
    `expires_time`,
    `redirect_uri`,
    `state`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `user_id` AS `user_id`,
    `user_type` AS `user_type`,
    `code` AS `code`,
    `client_id` AS `client_id`,
    `scopes` AS `scopes`,
    `expires_time` AS `expires_time`,
    `redirect_uri` AS `redirect_uri`,
    `state` AS `state`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`
FROM `ai-services`.`system_oauth2_code`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_oauth2_refresh_token */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_oauth2_refresh_token` (
    `id`,
    `snowflake_id`,
    `user_id`,
    `refresh_token`,
    `user_type`,
    `client_id`,
    `scopes`,
    `expires_time`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `user_id` AS `user_id`,
    `refresh_token` AS `refresh_token`,
    `user_type` AS `user_type`,
    `client_id` AS `client_id`,
    `scopes` AS `scopes`,
    `expires_time` AS `expires_time`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`
FROM `ai-services`.`system_oauth2_refresh_token`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_operate_log */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_operate_log` (
    `id`,
    `snowflake_id`,
    `trace_id`,
    `user_id`,
    `user_type`,
    `type`,
    `sub_type`,
    `biz_id`,
    `action`,
    `success`,
    `extra`,
    `request_method`,
    `request_url`,
    `user_ip`,
    `user_agent`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `trace_id` AS `trace_id`,
    `user_id` AS `user_id`,
    `user_type` AS `user_type`,
    `type` AS `type`,
    `sub_type` AS `sub_type`,
    `biz_id` AS `biz_id`,
    `action` AS `action`,
    `success` AS `success`,
    `extra` AS `extra`,
    `request_method` AS `request_method`,
    `request_url` AS `request_url`,
    `user_ip` AS `user_ip`,
    `user_agent` AS `user_agent`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`
FROM `ai-services`.`system_operate_log`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_post */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_post` (
    `id`,
    `snowflake_id`,
    `code`,
    `name`,
    `sort`,
    `status`,
    `remark`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `code` AS `code`,
    `name` AS `name`,
    `sort` AS `sort`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `remark` AS `remark`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`
FROM `ai-services`.`system_post`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_role */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_role` (
    `id`,
    `snowflake_id`,
    `name`,
    `code`,
    `sort`,
    `data_scope`,
    `data_scope_dept_ids`,
    `status`,
    `type`,
    `remark`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `name` AS `name`,
    `code` AS `code`,
    `sort` AS `sort`,
    `data_scope` AS `data_scope`,
    `data_scope_dept_ids` AS `data_scope_dept_ids`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `type` AS `type`,
    `remark` AS `remark`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`
FROM `ai-services`.`system_role`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_role_menu */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_role_menu` (
    `id`,
    `snowflake_id`,
    `role_id`,
    `menu_id`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `role_id` AS `role_id`,
    `menu_id` AS `menu_id`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`
FROM `ai-services`.`system_role_menu`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_sms_channel */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_sms_channel` (
    `id`,
    `snowflake_id`,
    `signature`,
    `code`,
    `status`,
    `remark`,
    `api_key`,
    `api_secret`,
    `callback_url`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `signature` AS `signature`,
    `code` AS `code`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `remark` AS `remark`,
    `api_key` AS `api_key`,
    `api_secret` AS `api_secret`,
    `callback_url` AS `callback_url`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_sms_channel`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_sms_code */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_sms_code` (
    `id`,
    `snowflake_id`,
    `mobile`,
    `code`,
    `create_ip`,
    `scene`,
    `today_index`,
    `used`,
    `used_time`,
    `used_ip`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `mobile` AS `mobile`,
    `code` AS `code`,
    `create_ip` AS `create_ip`,
    `scene` AS `scene`,
    `today_index` AS `today_index`,
    `used` AS `used`,
    `used_time` AS `used_time`,
    `used_ip` AS `used_ip`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`
FROM `ai-services`.`system_sms_code`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_sms_log */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_sms_log` (
    `id`,
    `snowflake_id`,
    `channel_id`,
    `channel_code`,
    `template_id`,
    `template_code`,
    `template_type`,
    `template_content`,
    `template_params`,
    `api_template_id`,
    `mobile`,
    `user_id`,
    `user_type`,
    `send_status`,
    `send_time`,
    `api_send_code`,
    `api_send_msg`,
    `api_request_id`,
    `api_serial_no`,
    `receive_status`,
    `receive_time`,
    `api_receive_code`,
    `api_receive_msg`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `channel_id` AS `channel_id`,
    `channel_code` AS `channel_code`,
    `template_id` AS `template_id`,
    `template_code` AS `template_code`,
    `template_type` AS `template_type`,
    `template_content` AS `template_content`,
    `template_params` AS `template_params`,
    `api_template_id` AS `api_template_id`,
    `mobile` AS `mobile`,
    `user_id` AS `user_id`,
    `user_type` AS `user_type`,
    `send_status` AS `send_status`,
    `send_time` AS `send_time`,
    `api_send_code` AS `api_send_code`,
    `api_send_msg` AS `api_send_msg`,
    `api_request_id` AS `api_request_id`,
    `api_serial_no` AS `api_serial_no`,
    `receive_status` AS `receive_status`,
    `receive_time` AS `receive_time`,
    `api_receive_code` AS `api_receive_code`,
    `api_receive_msg` AS `api_receive_msg`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_sms_log`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_sms_template */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_sms_template` (
    `id`,
    `snowflake_id`,
    `type`,
    `status`,
    `code`,
    `name`,
    `content`,
    `params`,
    `remark`,
    `api_template_id`,
    `channel_id`,
    `channel_code`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `type` AS `type`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `code` AS `code`,
    `name` AS `name`,
    `content` AS `content`,
    `params` AS `params`,
    `remark` AS `remark`,
    `api_template_id` AS `api_template_id`,
    `channel_id` AS `channel_id`,
    `channel_code` AS `channel_code`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_sms_template`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_social_client */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_social_client` (
    `id`,
    `snowflake_id`,
    `name`,
    `social_type`,
    `user_type`,
    `client_id`,
    `client_secret`,
    `agent_id`,
    `status`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `name` AS `name`,
    `social_type` AS `social_type`,
    `user_type` AS `user_type`,
    `client_id` AS `client_id`,
    `client_secret` AS `client_secret`,
    `agent_id` AS `agent_id`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`
FROM `ai-services`.`system_social_client`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_social_user */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_social_user` (
    `id`,
    `snowflake_id`,
    `type`,
    `openid`,
    `token`,
    `raw_token_info`,
    `nickname`,
    `avatar`,
    `raw_user_info`,
    `code`,
    `state`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `type` AS `type`,
    `openid` AS `openid`,
    `token` AS `token`,
    `raw_token_info` AS `raw_token_info`,
    `nickname` AS `nickname`,
    `avatar` AS `avatar`,
    `raw_user_info` AS `raw_user_info`,
    `code` AS `code`,
    `state` AS `state`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`
FROM `ai-services`.`system_social_user`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_social_user_bind */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_social_user_bind` (
    `id`,
    `snowflake_id`,
    `user_id`,
    `user_type`,
    `social_type`,
    `social_user_id`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `user_id` AS `user_id`,
    `user_type` AS `user_type`,
    `social_type` AS `social_type`,
    `social_user_id` AS `social_user_id`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`
FROM `ai-services`.`system_social_user_bind`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_tenant */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_tenant` (
    `id`,
    `snowflake_id`,
    `name`,
    `contact_user_id`,
    `contact_name`,
    `contact_mobile`,
    `status`,
    `website`,
    `package_id`,
    `expire_time`,
    `account_count`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `name` AS `name`,
    `contact_user_id` AS `contact_user_id`,
    `contact_name` AS `contact_name`,
    `contact_mobile` AS `contact_mobile`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `website` AS `website`,
    `package_id` AS `package_id`,
    `expire_time` AS `expire_time`,
    `account_count` AS `account_count`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_tenant`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_tenant_package */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_tenant_package` (
    `id`,
    `snowflake_id`,
    `name`,
    `status`,
    `remark`,
    `menu_ids`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `name` AS `name`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `remark` AS `remark`,
    `menu_ids` AS `menu_ids`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_tenant_package`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_user_post */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_user_post` (
    `id`,
    `snowflake_id`,
    `user_id`,
    `post_id`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `user_id` AS `user_id`,
    `post_id` AS `post_id`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`
FROM `ai-services`.`system_user_post`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_user_role */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_user_role` (
    `id`,
    `snowflake_id`,
    `user_id`,
    `role_id`,
    `created_at`,
    `create_time`,
    `updated_at`,
    `update_time`,
    `deleted`,
    `tenant_id`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `user_id` AS `user_id`,
    `role_id` AS `role_id`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    `create_time` AS `create_time`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    `update_time` AS `update_time`,
    `deleted` AS `deleted`,
    `tenant_id` AS `tenant_id`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`
FROM `ai-services`.`system_user_role`;

/*--------------------------------------------------*/
/* Migration SQL for table: system_users */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`system_users` (
    `id`,
    `snowflake_id`,
    `username`,
    `password`,
    `nickname`,
    `remark`,
    `dept_id`,
    `post_ids`,
    `email`,
    `mobile`,
    `sex`,
    `avatar`,
    `status`,
    `login_ip`,
    `login_date`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`,
    `level`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `username` AS `username`,
    `password` AS `password`,
    `nickname` AS `nickname`,
    `remark` AS `remark`,
    `dept_id` AS `dept_id`,
    `post_ids` AS `post_ids`,
    `email` AS `email`,
    `mobile` AS `mobile`,
    `sex` AS `sex`,
    `avatar` AS `avatar`,
    CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END AS `status`,
    `login_ip` AS `login_ip`,
    `login_date` AS `login_date`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`,
    NULL AS `level`
FROM `ai-services`.`system_users`;

/*--------------------------------------------------*/
/* Migration SQL for table: yudao_demo01_contact */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`yudao_demo01_contact` (
    `id`,
    `snowflake_id`,
    `name`,
    `sex`,
    `birthday`,
    `description`,
    `avatar`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `name` AS `name`,
    `sex` AS `sex`,
    `birthday` AS `birthday`,
    `description` AS `description`,
    `avatar` AS `avatar`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`
FROM `ai-services`.`yudao_demo01_contact`;

/*--------------------------------------------------*/
/* Migration SQL for table: yudao_demo02_category */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`yudao_demo02_category` (
    `id`,
    `snowflake_id`,
    `name`,
    `parent_id`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `name` AS `name`,
    `parent_id` AS `parent_id`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`
FROM `ai-services`.`yudao_demo02_category`;

/*--------------------------------------------------*/
/* Migration SQL for table: yudao_demo03_course */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`yudao_demo03_course` (
    `id`,
    `snowflake_id`,
    `student_id`,
    `name`,
    `score`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `student_id` AS `student_id`,
    `name` AS `name`,
    `score` AS `score`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`
FROM `ai-services`.`yudao_demo03_course`;

/*--------------------------------------------------*/
/* Migration SQL for table: yudao_demo03_grade */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`yudao_demo03_grade` (
    `id`,
    `snowflake_id`,
    `student_id`,
    `name`,
    `teacher`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `student_id` AS `student_id`,
    `name` AS `name`,
    `teacher` AS `teacher`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`
FROM `ai-services`.`yudao_demo03_grade`;

/*--------------------------------------------------*/
/* Migration SQL for table: yudao_demo03_student */
/*--------------------------------------------------*/
INSERT INTO `ai-services-new`.`yudao_demo03_student` (
    `id`,
    `snowflake_id`,
    `name`,
    `sex`,
    `birthday`,
    `description`,
    `created_at`,
    `created_by`,
    `updated_at`,
    `updated_by`,
    `is_deleted`,
    `deleted_at`,
    `deleted_by`,
    `tenant_id`
)
SELECT
    `id` AS `id`,
    CAST(`id` AS CHAR) AS `snowflake_id`,
    `name` AS `name`,
    `sex` AS `sex`,
    `birthday` AS `birthday`,
    `description` AS `description`,
    COALESCE(`create_time`, NOW()) AS `created_at`,
    COALESCE(`creator`, 'System') AS `created_by`,
    COALESCE(`update_time`, NOW()) AS `updated_at`,
    COALESCE(`updater`, 'System') AS `updated_by`,
    CAST(`deleted` AS UNSIGNED) AS `is_deleted`,
    NULL AS `deleted_at`,
    NULL AS `deleted_by`,
    `tenant_id` AS `tenant_id`
FROM `ai-services`.`yudao_demo03_student`;
