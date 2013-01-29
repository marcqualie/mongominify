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
	)

);