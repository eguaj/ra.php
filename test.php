<?php

include_once('ra.php');

/* Define a relation R1 with columns ('id', 'name', 'info') */
$r = new raRelation(
	'R1',
	array('id', 'name', 'info')
);

/* Project the ('id', 'name') columns from R1 */
$r = new raProjection(
	array('id', 'name'),
	$r
);

/* Select tuples from the previously projected R1 where 'id' is > 100 */
$r = new raSelection(
	array(
		new raAtom('id', '>', '100')
	),
	$r
);

/* Rename the 'id' column to 'identifier' */
$r = new raRename(
	array('id' => 'identifier'),
	$r
);

/* Print the resulting SQL statement */
print sprintf("%s;\n", $r->sql());

/* Same operations, but in one line */
$r = new raRename( array('id' => 'identifier'),
	new raSelection( array(new raAtom('id', '>', '100')),
		new raProjection( array('id', 'name'),
			new raRelation( 'R1',
				array('id', 'name', 'info')
			)
		)
	)
);

print sprintf("%s;\n", $r->sql());

