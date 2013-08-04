<?php

return array(

	'user_id' => array(
		'short' => 'u'
	),

	'email' => array(
		'short' => 'e'
	),

	'tags' => array(
		'short' => 't',
		'subset' => array(
			'slug' => array(
				'short' => 's'
			),
			'name' => array(
				'short' => 'n'
			)
		)
	),

	'role' => array(
		'type' => 'enum',
		'short' => 'r',
		'values' => array(
			0 => 'admin',
			1 => 'moderator',
			2 => 'user',
			3 => 'guest'
		)
	),

	'contact' => array(
		'short' => 'c',
		'subset' => array(
			'preferred' => array(
				'short' => 'a',
				'type' => 'enum',
				'values' => array('email', 'phone', 'post')
			),
			'email' => array(
				'short' => 'e',
				'subset' => array(
					'home' => array(
						'short' => 'h'
					),
					'work' => array(
						'short' => 'w',
						'subset' => array(
							'office' => array(
								'short' => 'o'
							),
							'mobile' => array(
								'short' => 'm'
							)
						)
					)
				)
			),
			'phone' => array(
				'short' => 'p',
				'subset' => array(
					'home' => array(
						'short' => 'h'
					),
					'work' => array(
						'short' => 'w'
					)
				)
			)
		)
	),

	'meta' => array(
		'short' => 'm'
	)

);
