

INSERT INTO `maestro_step_type` (`id`, `step_type`, `flex_field`, `is_interactive_step_type`) VALUES 
(1, 'Manual Web', NULL, 1),
(2, 'And', NULL, 0),
(4, 'Batch', NULL, 0),
(5, 'If', NULL, 0),
(6, 'batch function', NULL, 0),
(7, 'interactive function', NULL, 1),
(8, 'nexform', NULL, 1),
(9, 'Start', NULL, 0),
(10, 'End', NULL, 0),
(11, 'Set Process Variable', NULL, 0);

INSERT INTO `maestro_template` (`id`, `template_name`, `use_project`, `app_group`) VALUES 
(1, 'Test Workflow1', 0, 0);

INSERT INTO `maestro_template_assignment` (`id`, `template_data_id`, `uid`, `gid`, `process_variable`, `pre_notify_variable`, `post_notify_variable`, `reminder_notify_variable`) VALUES 
(7, 23, 0, 0, 4, 0, 0, 0);

INSERT INTO `maestro_template_data` (`id`, `template_id`, `logical_id`, `task_class_name`, `handler_id`, `first_task`, `taskname`, `assigned_by_variable`, `argument_variable`, `argument_process`, `operator`, `if_value`, `regenerate`, `regen_all_live_tasks`, `is_dynamic_form`, `dynamic_form_variable_id`, `is_dynamic_taskname`, `dynamic_taskname_variable_id`, `function`, `form_id`, `field_id`, `var_value`, `inc_value`, `var_to_set`, `optional_parm`, `reminder_interval`, `reminder_interval_variable`, `subsequent_reminder_interval`, `last_updated`, `pre_notify_subject`, `post_notify_subject`, `reminder_subject`, `pre_notify_message`, `post_notify_message`, `reminder_message`, `num_reminders`, `escalate_variable_id`, `offset_left`, `offset_top`, `surpress_first_notification`) VALUES 
(1, 1, 1, 'MaestroTaskTypeStart', 0, 1, 'Start', 0, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, '', 0, 0, NULL, 0, 0, '', 0, 0, 0, 0, NULL, NULL, NULL, '', '', '', 0, 0, 34, 259, 0),
(2, 1, 2, 'MaestroTaskTypeInteractivefunction', 0, 0, 'Set Variable for Test', 1, '0', '0', '0', '0', 0, 0, 0, 0, 0, 0, 'maestro_test_suite_set_var', 0, 0, NULL, 0, 0, '', 0, 0, 0, 0, NULL, NULL, NULL, '', '', '', 0, 0, 310, 258, 0),
(3, 1, 3, 'MaestroTaskTypeIf', 0, 0, 'Test for VAR1 &gt; 5', 0, '5', '0', '2', '5', 0, 0, 0, 0, 0, 0, '', 0, 0, NULL, 0, 0, '', 0, 0, 0, 0, NULL, NULL, NULL, '', '', '', 0, 0, 581, 260, 0),
(4, 1, 4, 'MaestroTaskTypeBatch', 0, 0, 'Variable is &gt; 5', 0, '0', '0', '0', '0', 0, 0, 0, 0, 0, 0, 'maestro_log_message', 0, 0, NULL, 0, 0, 'VAR1 is greater than 5', 0, 0, 0, 0, NULL, NULL, NULL, '', '', '', 0, 0, 579, 342, 0),
(5, 1, 5, 'MaestroTaskTypeBatch', 0, 0, 'Variable is not &gt; 5', 0, '0', '0', '0', '0', 0, 0, 0, 0, 0, 0, 'maestro_log_message', 0, 0, NULL, 0, 0, 'VAR1 is NOT greater than 5', 0, 0, 0, 0, NULL, NULL, NULL, '', '', '', 0, 0, 312, 341, 0),
(6, 1, 6, 'MaestroTaskTypeEnd', 0, 0, 'End', 0, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, '', 0, 0, NULL, 0, 0, '', 0, 0, 0, 0, NULL, NULL, NULL, '', '', '', 0, 0, 580, 439, 0);


INSERT INTO `maestro_template_data_next_step` (`id`, `template_data_from`, `template_data_to`, `template_data_to_false`) VALUES 
(1, 1, 2, 0),
(2, 2, 3, 0),
(3, 3, 4, 0),
(4, 3, 0, 5),
(5, 5, 6, 0),
(7, 4, 6, 0);


INSERT INTO `maestro_template_variables` (`id`, `template_id`, `variable_type_id`, `variable_name`, `variable_value`) VALUES 
(1, 1, 0, 'INITIATOR', ''),
(2, 1, 0, 'VAR1', '1');
