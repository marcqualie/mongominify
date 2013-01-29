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

	'contact' => array(
		'short' => 'c',
		'subset' => array(
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
	)

);