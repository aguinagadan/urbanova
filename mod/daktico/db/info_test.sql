INSERT INTO `mdl_aq_encuesta_data` (`id`, `pregunta`, `active`, `created_at`, `updated_at`) VALUES (NULL, '¿Qué tanto te gustó el curso?', '1', '0', '0');
INSERT INTO `mdl_aq_encuesta_data` (`id`, `pregunta`, `active`, `created_at`, `updated_at`) VALUES (NULL, '¿Cómo calificas el contenido?', '1', '0', '0');
INSERT INTO `mdl_aq_encuesta_data` (`id`, `pregunta`, `active`, `created_at`, `updated_at`) VALUES (NULL, '¿Cómo calificas la presentación?', '1', '0', '0');

INSERT INTO `mdl_aq_material_data` (`id`, `material_title`, `material_icon`, `link_file`, `format`, `active`, `created_at`, `updated_at`) VALUES (NULL, 'Nombre del primer archivo adjunto', 'attachment', 'http://www.africau.edu/images/default/sample.pdf', '.pdf', '1', '0', '0');
INSERT INTO `mdl_aq_material_data` (`id`, `material_title`, `material_icon`, `link_file`, `format`, `active`, `created_at`, `updated_at`) VALUES (NULL, 'Nombre del video', 'slow_motion_video', 'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4', '.mp4', '1', '0', '0');
INSERT INTO `mdl_aq_material_data` (`id`, `material_title`, `material_icon`, `link_file`, `format`, `active`, `created_at`, `updated_at`) VALUES (NULL, 'Nombre del segundo archivo', 'attachment', 'http://www.africau.edu/images/default/sample.pdf', '.pdf', '1', '0', '0');

INSERT INTO `mdl_aq_evaluacion_data` (`id`, `pregunta`, `active`, `created_at`, `updated_at`) VALUES (NULL, '¿Qué mar navegan los astronautas?', '1', '0', '0');
INSERT INTO `mdl_aq_evaluacion_data` (`id`, `pregunta`, `active`, `created_at`, `updated_at`) VALUES (NULL, 'El agua potable tiene color amarillo verdoso clandestino.', '1', '0', '0');

INSERT INTO `mdl_aq_evaluacion_options_data` (`id`, `opcion`, `is_valid`, `active`, `preguntaid`, `created_at`, `updated_at`) VALUES (NULL, 'Rios, lagos y lagunitas', '0', '1', '1', '0', '0');
INSERT INTO `mdl_aq_evaluacion_options_data` (`id`, `opcion`, `is_valid`, `active`, `preguntaid`, `created_at`, `updated_at`) VALUES (NULL, 'Océano atlantico', '0', '1', '1', '0', '0');
INSERT INTO `mdl_aq_evaluacion_options_data` (`id`, `opcion`, `is_valid`, `active`, `preguntaid`, `created_at`, `updated_at`) VALUES (NULL, 'Océano pacífico', '0', '1', '1', '0', '0');
INSERT INTO `mdl_aq_evaluacion_options_data` (`id`, `opcion`, `is_valid`, `active`, `preguntaid`, `created_at`, `updated_at`) VALUES (NULL, 'Ninguna de las anteriores', '1', '1', '1', '0', '0');
INSERT INTO `mdl_aq_evaluacion_options_data` (`id`, `opcion`, `is_valid`, `active`, `preguntaid`, `created_at`, `updated_at`) VALUES (NULL, 'Verdadero', '0', '1', '2', '0', '0');
INSERT INTO `mdl_aq_evaluacion_options_data` (`id`, `opcion`, `is_valid`, `active`, `preguntaid`, `created_at`, `updated_at`) VALUES (NULL, 'Falso', '1', '1', '2', '0', '0');

INSERT INTO `mdl_aq_iframe_page` (`id`, `iframe_link`) VALUES (NULL, 'https://daktico.com/');