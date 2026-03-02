<?php

/**
 * Markdown 26.03.1 Arcane Helper
 * MIT https://helpers.arcane.dev
**/

return function($content, $replace = []) {
  $content = is_null($content) ? '' : $content;

  if(is_array($content)) {
    $content = implode("\n", $content);
  } else if(substr(rtrim($content), -2) === 'md') {
    if(is_file($path = path(trim($content), true))) {
      $content = file_get_contents($path);
    }
  }

  if(!empty($replace)) {
    $content = strtr($content, $replace);
  }

  $includes = strpos($content, "@\x20");
  $content = explode("\n", $content);

  if($includes !== false) {
    $includes = preg_grep("/^\s*(?<!\s{4})@\s+(.+)$/", $content);

    foreach(array_reverse($includes, true) as $index => $include) {
      if(is_file($path = path(trim($include, "@\x20"), true))) {
        $include = file_get_contents($path);

        if(substr($path, -2) === 'md') {
          $include = array_filter(explode("\n", $include));
        } else {
          $spaces = str_repeat("\x20", 4);
          $include = preg_replace("/^/", $spaces, $include);
        }
      }

      array_splice($content, $index, 1, $include);
    }
  }

  $content = array_map('rtrim', $content);
  $content = array_values(array_filter($content));
  $markdown = [];

  foreach($content as $index => $line) {
    $next = next($content);

    if(str_ends_with($line, '}')) {
      if(preg_match('/\Q{\E.*?\Q}\E$/', $line, $found)) {
          $id = ' id ="' . trim($found[0], '{}') . '"';

          $line = preg_replace('/\Q{\E.*?\Q}\E$/', '', $line);
      }
    } else {
      $id = null;
    }

    foreach(['line', 'next'] as $variable) {
      $start[$variable] = ltrim($$variable)[0] ?? 0;
    }

    unset($format);

    if($start['line'] === '>') {
      $line = substr($line, strpos($line, '>') + 1);

      if(!isset($quote)) {
        $quote = 'blockquote';
        $markdown[] = "<{$quote}>";
      }
    }

    if(ctype_space(substr($line, 0, 4))) {
      $line = htmlentities(substr($line, 4));

      if(!isset($code)) {
        $code = 'pre';
        $markdown[] = "<{$code}><code>";
      } else {
        $format = "\n%s";
      }
    } else {
      preg_match("/^\s*(\#{1,6}|\+|\-|\*)\s+(.+)$/", $line, $matches);

      if(!empty($matches)) {
        list($element, $line) = [$matches[1], $matches[2]];

        if($element[0] === '#') {
          $length = strlen($element);
          $format = "<h{$length}>%s</h{$length}>";
        } else if(in_array($element, ['+', '-', '*'])) {
          $format = "<li>%s</li>";

          if(!isset($parent) || !isset($child)) {
            $list = $element === '*' ? 'ul' : 'ol';

            if($element === '-') {
              $list = "{$list} type=\"A\"";
            }

            if(!isset($parent)) {
              $format = "<{$list}>{$format}";
              $parent = $element;
            }

            if(!isset($child)) {
              if($start['line'] !== $parent) {
              $format = "<{$list}>{$format}";
              $child = $element;
              }
            }
          }

          if(isset($child) || isset($parent)) {
            if(!$next || trim($next[0])) {
              if(isset($child)) {
                if($start['next'] !== $child) {
                  $list = $child === '*' ? 'ul' : 'ol';
                  $format = "{$format}</{$list}>";

                  unset($child);
                }
              }

              if($start['next'] !== $parent) {
                $list = $parent === '*' ? 'ul' : 'ol';
                $format = "{$format}</{$list}>";

                unset($parent);
              }
            }
          }
        }
      } else {
        $line = ltrim($line);

        if($line === '---') {
          $format = '<hr />';
        } else {
          $format = '<p' . $id . '>%s</p>';
        }
      }

      foreach([
        '*' => "/(\A|\W)\*(?!\s)([^\*]+)(?<!\s)\*(?=\W|\Z)/U",
        '_' => "/(\A|\W)\_(?!\s)([^\_]+)(?<!\s)\_(?=\W|\Z)/U",
        '~' => "/(\A|\W)\~(?!\s)([^\~]+)(?<!\s)\~(?=\W|\Z)/U",
        '`' => "/(\A|\W)\`(?!\s)([^\`]+)(?<!\s)\`(?=\W|\Z)/U",
        '![' => "/(\A|\W)\!\[(.*)\]\((.*)\)(?=\W|\Z)/U",
        '](' => "/(\A|\W)\[(.*)\]\((.*)\)(?=\W|\Z)/U"
      ] as $search => $regex) {
        if(strpos($line, $search) !== false) {
          if($search === '`') {
            $line = preg_replace_callback($regex, function($match) {
              $match = htmlentities($match[1]);

              return str_replace('$2', $match, '$1<code>$2</code>$3');
            }, $line);
          } else {
            $html = [
              '*' => "$1<strong>$2</strong>$3",
              '_' => "$1<em>$2</em>$3",
              '~' => "$1<strike>$2</strike>$3",
              '![' => "$1<img src=\"$3\" alt=\"$2\" />$4",
              '](' => "$1<a href=\"$3\" target=\"_blank\">$2</a>$4"
            ];

            $line = preg_replace($regex, $html[$search], $line);
          }
        }
      }
    }

    $markdown[] = sprintf($format ?? '%s', $line);

    if(isset($code)) {
      if(!ctype_space(substr($next, 0, 4))) {
        $markdown[] = "</{$code}></code>";

        unset($code);
      }
    }

    if(isset($quote)) {
      if($start['next'] !== '>') {
        $markdown[] = "</{$quote}>";

        unset($quote);
      }
    }
  }

  return implode($markdown);
};

?>