How to regex phpstorm diff dump to php arrays

\[(-?\d)\] =>
to
'$1' =>

and then

=> (.)\n
to
=> '$1',\n

and then

\)
to
\),

and then, if needed

swap root's closing `),` by `);`.


optimize


Array\n\s*\(
to
array (

    \),\n
to
\),
