<?php return function ($in, $debugopt = 1) {
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => false,
            'prop' => false,
            'method' => false,
            'mustlok' => true,
            'mustsec' => true,
            'echo' => false,
            'debug' => $debugopt,
        ),
        'helpers' => array(            'msg' => function( array $args, array $named ) {
		$message = null;
		$str = array_shift( $args );

		return wfMessage( $str )->params( $args )->text();
	},
),
        'blockhelpers' => array(),
        'hbhelpers' => array(),
        'partials' => array(),
        'scopes' => array($in),
        'sp_vars' => array('root' => $in),
'funcs' => array(
    'ch' => function ($cx, $ch, $vars, $op) {
        return $cx['funcs']['chret'](call_user_func_array($cx['helpers'][$ch], $vars), $op);
    },
    'sec' => function ($cx, $v, $in, $each, $cb, $inv = null) {
        $isAry = is_array($v);
        $isTrav = $v instanceof Traversable;
        $loop = $each;
        $keys = null;
        $last = null;
        $isObj = false;

        if ($isAry && $inv !== null && count($v) === 0) {
            return $inv($cx, $in);
        }

        // #var, detect input type is object or not
        if (!$loop && $isAry) {
            $keys = array_keys($v);
            $loop = (count(array_diff_key($v, array_keys($keys))) == 0);
            $isObj = !$loop;
        }

        if (($loop && $isAry) || $isTrav) {
            if ($each && !$isTrav) {
                // Detect input type is object or not when never done once
                if ($keys == null) {
                    $keys = array_keys($v);
                    $isObj = (count(array_diff_key($v, array_keys($keys))) > 0);
                }
            }
            $ret = array();
            $cx['scopes'][] = $in;
            $i = 0;
            if ($cx['flags']['spvar']) {
                $old_spvar = $cx['sp_vars'];
                $cx['sp_vars'] = array(
                    '_parent' => $old_spvar,
                    'root' => $old_spvar['root'],
                );
                if (!$isTrav) {
                    $last = count($keys) - 1;
                }
            }
            foreach ($v as $index => $raw) {
                if ($cx['flags']['spvar']) {
                    $cx['sp_vars']['first'] = ($i === 0);
                    if ($isObj || $isTrav) {
                        $cx['sp_vars']['key'] = $index;
                        $cx['sp_vars']['index'] = $i;
                    } else {
                        $cx['sp_vars']['last'] = ($i == $last);
                        $cx['sp_vars']['index'] = $index;
                    }
                    $i++;
                }
                $ret[] = $cb($cx, $raw);
            }
            if ($cx['flags']['spvar']) {
                if ($isObj) {
                    unset($cx['sp_vars']['key']);
                } else {
                    unset($cx['sp_vars']['last']);
                }
                unset($cx['sp_vars']['index']);
                unset($cx['sp_vars']['first']);
                $cx['sp_vars'] = $old_spvar;
            }
            array_pop($cx['scopes']);
            return join('', $ret);
        }
        if ($each) {
            if ($inv !== null) {
                $cx['scopes'][] = $in;
                $ret = $inv($cx, $v);
                array_pop($cx['scopes']);
                return $ret;
            }
            return '';
        }
        if ($isAry) {
            if ($cx['flags']['mustsec']) {
                $cx['scopes'][] = $v;
            }
            $ret = $cb($cx, $v);
            if ($cx['flags']['mustsec']) {
                array_pop($cx['scopes']);
            }
            return $ret;
        }

        if ($v === true) {
            return $cb($cx, $in);
        }

        if (!is_null($v) && ($v !== false)) {
            return $cb($cx, $v);
        }

        if ($inv !== null) {
            return $inv($cx, $in);
        }

        return '';
    },
    'v' => function ($cx, $base, $path) {
        $count = count($cx['scopes']);
        while ($base) {
            $v = $base;
            foreach ($path as $name) {
                if (is_array($v) && isset($v[$name])) {
                    $v = $v[$name];
                    continue;
                }
                if (is_object($v)) {
                    if ($cx['flags']['prop'] && isset($v->$name)) {
                        $v = $v->$name;
                        continue;
                    }
                    if ($cx['flags']['method'] && is_callable(array($v, $name))) {
                        $v = $v->$name();
                        continue;
                    }
                }
                if ($cx['flags']['mustlok']) {
                    unset($v);
                    break;
                }
                return null;
            }
            if (isset($v)) {
                return $v;
            }
            $count--;
            if ($count >= 0) {
                $base = $cx['scopes'][$count];
            } else {
                return null;
            }
        }
    },
    'chret' => function ($ret, $op) {
        if (is_array($ret)) {
            if (isset($ret[1]) && $ret[1]) {
                $op = $ret[1];
            }
            $ret = $ret[0];
        }

        switch ($op) {
            case 'enc':
                return htmlentities($ret, ENT_QUOTES, 'UTF-8');
            case 'encq':
                return preg_replace('/&#039;/', '&#x27;', htmlentities($ret, ENT_QUOTES, 'UTF-8'));
        }
        return $ret;
    },
)

    );
    return '<div class="ooui-playground-doc">
	<table class="wikitable sortable">
	<thead>
		<tr>
			<th>'.$cx['funcs']['ch']($cx, 'msg', array(array('ooui-playground-parameter-name'),array()), 'enc').'</th>
			<th>'.$cx['funcs']['ch']($cx, 'msg', array(array('ooui-playground-parameter-type'),array()), 'enc').'</th>
			<th>'.$cx['funcs']['ch']($cx, 'msg', array(array('ooui-playground-parameter-desc'),array()), 'enc').'</th>
		</tr>
	</thead>
	<tbody>
'.$cx['funcs']['sec']($cx, $in, $in, true, function($cx, $in) {return '		<tr>
			<td>'.htmlentities((string)$cx['funcs']['v']($cx, $in, array('name')), ENT_QUOTES, 'UTF-8').'</td>
			<td>
				<ul>
'.$cx['funcs']['sec']($cx, $cx['funcs']['v']($cx, $in, array('types')), $in, true, function($cx, $in) {return '					<li>'.htmlentities((string)$in, ENT_QUOTES, 'UTF-8').'</li>
';}).'				</ul>
			</td>
			<td>'.htmlentities((string)$cx['funcs']['v']($cx, $in, array('description')), ENT_QUOTES, 'UTF-8').'</td>
		</tr>
';}).'	</tbody>
	</table>
</div>
';
}
?>