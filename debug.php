<?php $pugModule = [
  'Phug\\Formatter\\Format\\BasicFormat::dependencies_storage' => 'pugModule',
  'Phug\\Formatter\\Format\\BasicFormat::helper_prefix' => 'Phug\\Formatter\\Format\\BasicFormat::',
  'Phug\\Formatter\\Format\\BasicFormat::get_helper' => function ($name) use (&$pugModule) {
    $dependenciesStorage = $pugModule['Phug\\Formatter\\Format\\BasicFormat::dependencies_storage'];
    $prefix = $pugModule['Phug\\Formatter\\Format\\BasicFormat::helper_prefix'];
    $format = $pugModule['Phug\\Formatter\\Format\\BasicFormat::dependencies_storage'];

                            if (!isset($$dependenciesStorage)) {
                                return $format->getHelper($name);
                            }

                            $storage = $$dependenciesStorage;

                            if (!array_key_exists($prefix.$name, $storage) &&
                                !isset($storage[$prefix.$name])
                            ) {
                                throw new \Exception(
                                    var_export($name, true).
                                    ' dependency not found in the namespace: '.
                                    var_export($prefix, true)
                                );
                            }

                            return $storage[$prefix.$name];
                        },
  'Phug\\Formatter\\Format\\BasicFormat::pattern' => function ($pattern) use (&$pugModule) {

                    $args = func_get_args();
                    $function = 'sprintf';
                    if (is_callable($pattern)) {
                        $function = $pattern;
                        $args = array_slice($args, 1);
                    }

                    return call_user_func_array($function, $args);
                },
  'Phug\\Formatter\\Format\\BasicFormat::patterns.html_text_escape' => 'htmlspecialchars',
  'Phug\\Formatter\\Format\\BasicFormat::pattern.html_text_escape' => function () use (&$pugModule) {
    $proceed = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern'];
    $pattern = $pugModule['Phug\\Formatter\\Format\\BasicFormat::patterns.html_text_escape'];

                    $args = func_get_args();
                    array_unshift($args, $pattern);

                    return call_user_func_array($proceed, $args);
                },
  'Phug\\Formatter\\Format\\BasicFormat::available_attribute_assignments' => array (
  0 => 'class',
  1 => 'style',
),
  'Phug\\Formatter\\Format\\BasicFormat::patterns.attribute_pattern' => ' %s="%s"',
  'Phug\\Formatter\\Format\\BasicFormat::pattern.attribute_pattern' => function () use (&$pugModule) {
    $proceed = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern'];
    $pattern = $pugModule['Phug\\Formatter\\Format\\BasicFormat::patterns.attribute_pattern'];

                    $args = func_get_args();
                    array_unshift($args, $pattern);

                    return call_user_func_array($proceed, $args);
                },
  'Phug\\Formatter\\Format\\BasicFormat::patterns.boolean_attribute_pattern' => ' %s="%s"',
  'Phug\\Formatter\\Format\\BasicFormat::pattern.boolean_attribute_pattern' => function () use (&$pugModule) {
    $proceed = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern'];
    $pattern = $pugModule['Phug\\Formatter\\Format\\BasicFormat::patterns.boolean_attribute_pattern'];

                    $args = func_get_args();
                    array_unshift($args, $pattern);

                    return call_user_func_array($proceed, $args);
                },
  'Phug\\Formatter\\Format\\BasicFormat::attribute_assignments' => function (&$attributes, $name, $value) use (&$pugModule) {
    $availableAssignments = $pugModule['Phug\\Formatter\\Format\\BasicFormat::available_attribute_assignments'];
    $getHelper = $pugModule['Phug\\Formatter\\Format\\BasicFormat::get_helper'];

                    if (!in_array($name, $availableAssignments)) {
                        return $value;
                    }

                    $helper = $getHelper($name.'_attribute_assignment');

                    return $helper($attributes, $value);
                },
  'Phug\\Formatter\\Format\\BasicFormat::attribute_assignment' => function (&$attributes, $name, $value) use (&$pugModule) {
    $attributeAssignments = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attribute_assignments'];

                    if (isset($name) && $name !== '') {
                        $result = $attributeAssignments($attributes, $name, $value);
                        if (($result !== null && $result !== false && ($result !== '' || $name !== 'class'))) {
                            $attributes[$name] = $result;
                        }
                    }
                },
  'Phug\\Formatter\\Format\\BasicFormat::merge_attributes' => function () use (&$pugModule) {
    $attributeAssignment = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attribute_assignment'];

                    $attributes = [];
                    foreach (array_filter(func_get_args(), 'is_array') as $input) {
                        foreach ($input as $name => $value) {
                            $attributeAssignment($attributes, $name, $value);
                        }
                    }

                    return $attributes;
                },
  'Phug\\Formatter\\Format\\BasicFormat::attributes_mapping' => array (
),
  'Phug\\Formatter\\Format\\BasicFormat::attributes_assignment' => function () use (&$pugModule) {
    $attrMapping = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_mapping'];
    $mergeAttr = $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'];
    $pattern = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern'];
    $escape = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern.html_text_escape'];
    $attr = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern.attribute_pattern'];
    $bool = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern.boolean_attribute_pattern'];

                        $attributes = call_user_func_array($mergeAttr, func_get_args());
                        $code = '';
                        foreach ($attributes as $originalName => $value) {
                            if ($value !== null && $value !== false && ($value !== '' || $originalName !== 'class')) {
                                $name = isset($attrMapping[$originalName])
                                    ? $attrMapping[$originalName]
                                    : $originalName;
                                if ($value === true) {
                                    $code .= $pattern($bool, $name, $name);

                                    continue;
                                }
                                if (is_array($value) || is_object($value) &&
                                    !method_exists($value, '__toString')) {
                                    $value = json_encode($value);
                                }

                                $code .= $pattern($attr, $name, $value);
                            }
                        }

                        return $code;
                    },
  'Phug\\Formatter\\Format\\BasicFormat::class_attribute_assignment' => function (&$attributes, $value) use (&$pugModule) {

            $split = function ($input) {
                return preg_split('/(?<![\[\{\<\=\%])\s+(?![\]\}\>\=\%])/', strval($input));
            };
            $classes = isset($attributes['class']) ? array_filter($split($attributes['class'])) : [];
            foreach ((array) $value as $key => $input) {
                if (!is_string($input) && is_string($key)) {
                    if (!$input) {
                        continue;
                    }

                    $input = $key;
                }
                foreach ($split($input) as $class) {
                    if (!in_array($class, $classes)) {
                        $classes[] = $class;
                    }
                }
            }

            return implode(' ', $classes);
        },
  'Phug\\Formatter\\Format\\BasicFormat::style_attribute_assignment' => function (&$attributes, $value) use (&$pugModule) {

            if (is_string($value) && mb_substr($value, 0, 7) === '{&quot;') {
                $value = json_decode(htmlspecialchars_decode($value));
            }
            $styles = isset($attributes['style']) ? array_filter(explode(';', $attributes['style'])) : [];
            foreach ((array) $value as $propertyName => $propertyValue) {
                if (!is_int($propertyName)) {
                    $propertyValue = $propertyName.':'.$propertyValue;
                }
                $styles[] = $propertyValue;
            }

            return implode(';', $styles);
        },
]; ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['bemto_custom_inline_tag'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'customTag', null], [false, 'self_closing', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(11);
// PUG_DEBUG:11
 ?><?php self_closing = self_closing || false ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(12);
// PUG_DEBUG:12
 ?><<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(13);
// PUG_DEBUG:13
 ?><?= htmlspecialchars((is_bool($_pug_temp = customTag) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(21);
// PUG_DEBUG:21
 ?><?php if (attributes) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(20);
// PUG_DEBUG:20
 ?><?php for (var attribute in attributes) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(19);
// PUG_DEBUG:19
 ?><?php if (attributes.hasOwnProperty(attribute) && attributes[attribute] !== false && attributes[attribute] !== undefined) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(14);
// PUG_DEBUG:14
 ?> <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(15);
// PUG_DEBUG:15
 ?><?= htmlspecialchars((is_bool($_pug_temp = attribute) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(16);
// PUG_DEBUG:16
 ?>="<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(17);
// PUG_DEBUG:17
 ?><?= (is_bool($_pug_temp = attributes[attribute] === true ? attribute : attributes[attribute]) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(18);
// PUG_DEBUG:18
 ?>"<?php } ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(23);
// PUG_DEBUG:23
 }  if (self_closing) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(22);
// PUG_DEBUG:22
 ?>/><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(24);
// PUG_DEBUG:24
 ?>><?= (is_bool($_pug_temp = $__pug_children(get_defined_vars())) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(25);
// PUG_DEBUG:25
 ?></<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(26);
// PUG_DEBUG:26
 ?><?= htmlspecialchars((is_bool($_pug_temp = customTag) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(27);
// PUG_DEBUG:27
 ?>><?php } ?><?php
}; ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['bemto_tag'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'tag', null], [false, 'tagMetadata', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(45);
// PUG_DEBUG:45
 ?><?php var settings = get_bemto_settings() ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(46);
// PUG_DEBUG:46
 ?><?php tagMetadata = tagMetadata || {} ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(47);
// PUG_DEBUG:47
 ?><?php var newTag = tag || settings['default_tag'] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(48);
// PUG_DEBUG:48
 ?><?php var contextIndex = bemto_chain_contexts.length ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(55);
// PUG_DEBUG:55
 ?><?php if (!tag) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(50);
// PUG_DEBUG:50
 ?><?php if (bemto_chain_contexts[contextIndex-1] === 'inline') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(49);
// PUG_DEBUG:49
 ?><?php newTag = 'span' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(52);
// PUG_DEBUG:52
 }  elseif (bemto_chain_contexts[contextIndex-1] === 'list') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(51);
// PUG_DEBUG:51
 ?><?php newTag = 'li' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(54);
// PUG_DEBUG:54
 }  elseif (bemto_chain_contexts[contextIndex-1] === 'optionlist') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(53);
// PUG_DEBUG:53
 ?><?php newTag = 'option' ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(64);
// PUG_DEBUG:64
 }  if (!tag || tag == 'span' || tag == 'div') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(57);
// PUG_DEBUG:57
 ?><?php if (attributes.href) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(56);
// PUG_DEBUG:56
 ?><?php newTag = 'a' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(59);
// PUG_DEBUG:59
 }  if (attributes.for) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(58);
// PUG_DEBUG:58
 ?><?php newTag = 'label' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(61);
// PUG_DEBUG:61
 }  if (attributes.type) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(60);
// PUG_DEBUG:60
 ?><?php newTag = block ? 'button' : 'input' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(63);
// PUG_DEBUG:63
 }  elseif (attributes.src) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(62);
// PUG_DEBUG:62
 ?><?php newTag = 'img' ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(66);
// PUG_DEBUG:66
 }  if (bemto_chain_contexts[contextIndex-1] === 'list' && newTag !== 'li') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(65);
// PUG_DEBUG:65
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(69);
// PUG_DEBUG:69
 }  elseif (bemto_chain_contexts[contextIndex-1] !== 'list' && bemto_chain_contexts[contextIndex-1] !== 'pseudo-list' && newTag === 'li') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(67);
// PUG_DEBUG:67
 ?><ul><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(68);
// PUG_DEBUG:68
 ?><?php bemto_chain_contexts[bemto_chain_contexts.length] = 'pseudo-list' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(72);
// PUG_DEBUG:72
 }  elseif (bemto_chain_contexts[contextIndex-1] === 'pseudo-list' && newTag !== 'li') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(70);
// PUG_DEBUG:70
 ?></ul><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(71);
// PUG_DEBUG:71
 ?><?php bemto_chain_contexts = bemto_chain_contexts.splice(0,bemto_chain_contexts.length-1) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(73);
// PUG_DEBUG:73
 }  var content_type = tagMetadata.content_type || get_bemto_tag_content_type(newTag) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(74);
// PUG_DEBUG:74
 ?><?php bemto_chain_contexts[bemto_chain_contexts.length] = content_type ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(88);
// PUG_DEBUG:88
 ?><?php if (newTag == 'img') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(76);
// PUG_DEBUG:76
 ?><?php if (attributes.alt && !attributes.title) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(75);
// PUG_DEBUG:75
 ?><?php attributes.title = '' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(78);
// PUG_DEBUG:78
 }  if (attributes.title && !attributes.alt) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(77);
// PUG_DEBUG:77
 ?><?php attributes.alt = attributes.title ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(80);
// PUG_DEBUG:80
 }  if (!attributes.alt) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(79);
// PUG_DEBUG:79
 ?><?php attributes.alt = '' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(82);
// PUG_DEBUG:82
 }  if (attributes.alt === '') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(81);
// PUG_DEBUG:81
 ?><?php attributes.role = 'presentation' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(87);
// PUG_DEBUG:87
 }  if (!attributes.src) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(84);
// PUG_DEBUG:84
 ?><?php if (settings.nosrc_substitute === true) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(83);
// PUG_DEBUG:83
 ?><?php attributes.src = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(86);
// PUG_DEBUG:86
 }  elseif (settings.nosrc_substitute) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(85);
// PUG_DEBUG:85
 ?><?php attributes.src = settings.nosrc_substitute ?><?php } ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(91);
// PUG_DEBUG:91
 }  if (newTag == 'input') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(90);
// PUG_DEBUG:90
 ?><?php if (!attributes.type) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(89);
// PUG_DEBUG:89
 ?><?php attributes.type = "text" ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(94);
// PUG_DEBUG:94
 }  if (newTag == 'main') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(93);
// PUG_DEBUG:93
 ?><?php if (!attributes.role) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(92);
// PUG_DEBUG:92
 ?><?php attributes.role = 'main' ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(97);
// PUG_DEBUG:97
 }  if (newTag == 'html') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(95);
// PUG_DEBUG:95
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(96);
// PUG_DEBUG:96
 ?><!DOCTYPE html>

  +bemto_custom_tag(newTag, tagMetadata)&attributes(attributes)
    if block
      block

  //- Closing all the wrapper tails
  if bemto_chain_contexts[contextIndex-1] === 'list' && newTag != 'li'
    | </li>
<?php };
}; ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['b'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'options', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(101);
// PUG_DEBUG:101
 ?><?php var settings = get_bemto_settings() ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(103);
// PUG_DEBUG:103
 ?><?php if (options && options.prefix !== undefined) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(102);
// PUG_DEBUG:102
 ?><?php settings.prefix = options.prefix ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(104);
// PUG_DEBUG:104
 }  var tag = options && options.tag || ( typeof options == 'string' ? options : '') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(105);
// PUG_DEBUG:105
 ?><?php var isElement = options && options.isElement ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(106);
// PUG_DEBUG:106
 ?><?php var tagMetadata = options && options.metadata ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(107);
// PUG_DEBUG:107
 ?><?php var block_sets_context = false ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(205);
// PUG_DEBUG:205
 ?><?php if (attributes.class) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(108);
// PUG_DEBUG:108
 ?><?php var bemto_classes = attributes.class ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(110);
// PUG_DEBUG:110
 ?><?php if (bemto_classes instanceof Array) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(109);
// PUG_DEBUG:109
 ?><?php bemto_classes = bemto_classes.join(' ') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(111);
// PUG_DEBUG:111
 }  bemto_classes = bemto_classes.split(' ') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(112);
// PUG_DEBUG:112
 ?><?php var bemto_objects = [] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(113);
// PUG_DEBUG:113
 ?><?php var is_first_object = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(114);
// PUG_DEBUG:114
 ?><?php var new_context = [] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(178);
// PUG_DEBUG:178
 ?><?php foreach (bemto_classes as $i => $klass) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(115);
// PUG_DEBUG:115
 ?><?php var bemto_object = {} ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(116);
// PUG_DEBUG:116
 ?><?php var prev_object = bemto_objects[bemto_objects.length - 1] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(117);
// PUG_DEBUG:117
 ?><?php var sets_context = false ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(120);
// PUG_DEBUG:120
 ?><?php if (klass.match(/^[A-Z-]+[A-Z0-9-]?$/)) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(118);
// PUG_DEBUG:118
 ?><?php tag = klass.toLowerCase() ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(119);
// PUG_DEBUG:119
 ?><?php continue ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(122);
// PUG_DEBUG:122
 }  if (is_first_object && isElement) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(121);
// PUG_DEBUG:121
 ?><?php bemto_object['context'] = bemto_chain[bemto_chain.length - 1] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(123);
// PUG_DEBUG:123
 }  var modifier_class = klass.match(new RegExp('^(?!' + settings['element'] + '[A-Za-z0-9])' + settings['modifier'] + '(.+)$')) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(128);
// PUG_DEBUG:128
 ?><?php if (modifier_class && prev_object && prev_object.name) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(125);
// PUG_DEBUG:125
 ?><?php if (!prev_object['modifiers']) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(124);
// PUG_DEBUG:124
 ?><?php prev_object['modifiers'] = [] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(126);
// PUG_DEBUG:126
 }  prev_object.modifiers.push(modifier_class[1]) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(127);
// PUG_DEBUG:127
 ?><?php continue ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(129);
// PUG_DEBUG:129
 }  var element_class = klass.match(new RegExp('^(?!' + settings['modifier'] + '[A-Za-z0-9])' + settings['element'] + '(.+)$')) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(132);
// PUG_DEBUG:132
 ?><?php if (element_class) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(130);
// PUG_DEBUG:130
 ?><?php bemto_object['context'] = bemto_chain[bemto_chain.length - 1] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(131);
// PUG_DEBUG:131
 ?><?php klass = element_class[1] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(133);
// PUG_DEBUG:133
 }  var name_with_context = klass.match(new RegExp('^(.*[A-Za-z0-9])(?!' + settings['modifier'] + '$)' + settings['element'] + '$')) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(139);
// PUG_DEBUG:139
 ?><?php if (name_with_context) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(134);
// PUG_DEBUG:134
 ?><?php klass = name_with_context[1] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(135);
// PUG_DEBUG:135
 ?><?php bemto_object['is_context'] = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(136);
// PUG_DEBUG:136
 ?><?php sets_context = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(137);
// PUG_DEBUG:137
 ?><?php block_sets_context = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(138);
// PUG_DEBUG:138
 ?><?php isElement = false ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(140);
// PUG_DEBUG:140
 }  var name_with_modifier = klass.match(new RegExp('^(.*?[A-Za-z0-9])(?!' + settings['element'] + '[A-Za-z0-9])' + settings['modifier'] + '(.+)$')) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(145);
// PUG_DEBUG:145
 ?><?php if (name_with_modifier) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(141);
// PUG_DEBUG:141
 ?><?php klass = name_with_modifier[1] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(143);
// PUG_DEBUG:143
 ?><?php if (!bemto_object['modifiers']) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(142);
// PUG_DEBUG:142
 ?><?php bemto_object['modifiers'] = [] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(144);
// PUG_DEBUG:144
 }  bemto_object.modifiers.push(name_with_modifier[2]) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(146);
// PUG_DEBUG:146
 }  var found_prefix = '' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(147);
// PUG_DEBUG:147
 ?><?php var prefix_regex_string = '()?' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(166);
// PUG_DEBUG:166
 ?><?php if (settings.prefix) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(148);
// PUG_DEBUG:148
 ?><?php var prefix = settings.prefix ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(150);
// PUG_DEBUG:150
 ?><?php if (typeof prefix === 'string') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(149);
// PUG_DEBUG:149
 ?><?php prefix = { '': prefix } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(151);
// PUG_DEBUG:151
 }  var prefix_regex_test = [] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(158);
// PUG_DEBUG:158
 ?><?php if (prefix instanceof Object) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(156);
// PUG_DEBUG:156
 ?><?php foreach (prefix as $key => $value) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(153);
// PUG_DEBUG:153
 ?><?php if (typeof key === 'string' && key != '' && prefix_regex_test.indexOf(key) == -1) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(152);
// PUG_DEBUG:152
 ?><?php prefix_regex_test.push(key) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(155);
// PUG_DEBUG:155
 }  if (typeof value === 'string' && value != '' && prefix_regex_test.indexOf(value) == -1) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(154);
// PUG_DEBUG:154
 ?><?php prefix_regex_test.push(value) ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(157);
// PUG_DEBUG:157
 }  prefix_regex_string = '(' + prefix_regex_test.join('|') + ')?' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(159);
// PUG_DEBUG:159
 }  var name_with_prefix = klass.match(new RegExp('^' + prefix_regex_string + '([A-Za-z0-9]+.*)$')) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(165);
// PUG_DEBUG:165
 ?><?php if (name_with_prefix) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(160);
// PUG_DEBUG:160
 ?><?php klass = name_with_prefix[2] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(161);
// PUG_DEBUG:161
 ?><?php found_prefix = name_with_prefix[1] || '' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(162);
// PUG_DEBUG:162
 ?><?php found_prefix = prefix[found_prefix] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(164);
// PUG_DEBUG:164
 ?><?php if (found_prefix === undefined || found_prefix === true) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(163);
// PUG_DEBUG:163
 ?><?php found_prefix = name_with_prefix[1] ?><?php } ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(167);
// PUG_DEBUG:167
 }  bemto_object['prefix'] = (found_prefix || '').replace(/\-/g, '%DASH%').replace(/\_/g, '%UNDERSCORE%') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(169);
// PUG_DEBUG:169
 ?><?php if (sets_context && klass.match(/^[a-zA-Z0-9]+.*/)) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(168);
// PUG_DEBUG:168
 ?><?php new_context.push(bemto_object.context ? (bemto_object.context + settings['element'] + klass) : (bemto_object.prefix + klass)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(170);
// PUG_DEBUG:170
 }  bemto_object['name'] = klass ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(171);
// PUG_DEBUG:171
 ?><?php is_first_object = false ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(176);
// PUG_DEBUG:176
 ?><?php if (bemto_object.context && bemto_object.context.length > 1) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(175);
// PUG_DEBUG:175
 ?><?php foreach (bemto_object.context as $i => $subcontext) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(172);
// PUG_DEBUG:172
 ?><?php var sub_object = clone(bemto_object) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(173);
// PUG_DEBUG:173
 ?><?php sub_object['context'] = [subcontext] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(174);
// PUG_DEBUG:174
 ?><?php bemto_objects.push(sub_object) ?><?php } ?><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(177);
// PUG_DEBUG:177
 ?><?php bemto_objects.push(bemto_object) ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(182);
// PUG_DEBUG:182
 }  if (!isElement && !new_context.length && bemto_objects[0] && bemto_objects[0].name && bemto_objects[0].name.match(/^[a-zA-Z0-9]+.*/)) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(179);
// PUG_DEBUG:179
 ?><?php bemto_objects[0]['is_context'] = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(180);
// PUG_DEBUG:180
 ?><?php new_context.push(bemto_objects[0].context ? (bemto_objects[0].context + settings['element'] + bemto_objects[0].name) : (bemto_objects[0].prefix + bemto_objects[0].name)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(181);
// PUG_DEBUG:181
 ?><?php block_sets_context = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(189);
// PUG_DEBUG:189
 }  if (new_context.length) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(187);
// PUG_DEBUG:187
 ?><?php if (settings.flat_elements) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(186);
// PUG_DEBUG:186
 ?><?php foreach (new_context as $i => $subcontext) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(183);
// PUG_DEBUG:183
 ?><?php var context_with_element = subcontext.match(new RegExp('^(.*?[A-Za-z0-9])(?!' + settings['modifier'] + '[A-Za-z0-9])' + settings['element'] + '.+$')) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(185);
// PUG_DEBUG:185
 ?><?php if (context_with_element) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(184);
// PUG_DEBUG:184
 ?><?php new_context[i] = context_with_element[1] ?><?php } ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(188);
// PUG_DEBUG:188
 }  bemto_chain[bemto_chain.length] = new_context ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(203);
// PUG_DEBUG:203
 }  if (bemto_objects.length) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(190);
// PUG_DEBUG:190
 ?><?php var new_classes = [] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(199);
// PUG_DEBUG:199
 ?><?php foreach (bemto_objects as $bemto_object) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(198);
// PUG_DEBUG:198
 ?><?php if (bemto_object.name) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(191);
// PUG_DEBUG:191
 ?><?php var start = bemto_object.prefix ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(193);
// PUG_DEBUG:193
 ?><?php if (bemto_object.context) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(192);
// PUG_DEBUG:192
 ?><?php start = bemto_object.context + settings.output_element ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(194);
// PUG_DEBUG:194
 }  new_classes.push(start + bemto_object.name) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(197);
// PUG_DEBUG:197
 ?><?php if (bemto_object.modifiers) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(196);
// PUG_DEBUG:196
 ?><?php foreach (bemto_object.modifiers as $modifier) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(195);
// PUG_DEBUG:195
 ?><?php new_classes.push(start + bemto_object.name + settings.output_modifier + modifier) ?><?php } ?><?php } ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(200);
// PUG_DEBUG:200
 }  var delimiter = settings.class_delimiter ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(201);
// PUG_DEBUG:201
 ?><?php delimiter = delimiter ? (' ' + delimiter + ' ') : ' ' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(202);
// PUG_DEBUG:202
 ?><?php attributes.class = new_classes.join(delimiter).replace(/%DASH%/g, '-').replace(/%UNDERSCORE%/g, '_') ?><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(204);
// PUG_DEBUG:204
 ?><?php attributes.class = undefined ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(206);
// PUG_DEBUG:206
 }  if (block) { ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'bemto_tag';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, array_merge([], attributes), [[false, tag], [false, tagMetadata]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    ?><?= (is_bool($_pug_temp = $__pug_children(get_defined_vars())) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php
}); ?><?php } else { ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'bemto_tag';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](false, array_merge([], attributes), [[false, tag], [false, tagMetadata]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    ?><?php
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(208);
// PUG_DEBUG:208
 }  if (!isElement && block_sets_context) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(207);
// PUG_DEBUG:207
 ?><?php bemto_chain = bemto_chain.splice(0,bemto_chain.length-1) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(209);
// PUG_DEBUG:209
 }  bemto_chain_contexts = bemto_chain_contexts.splice(0,bemto_chain_contexts.length-1) ?><?php
}; ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['e'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'options', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(211);
// PUG_DEBUG:211
 ?><?php if (options && typeof options == 'string') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(210);
// PUG_DEBUG:210
 ?><?php options = { 'tag': options } ?><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(212);
// PUG_DEBUG:212
 ?><?php options = options || {} ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(213);
// PUG_DEBUG:213
 }  options['isElement'] = true ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, array_merge([], attributes), [[false, options]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    ?><?= (is_bool($_pug_temp = $__pug_children(get_defined_vars())) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(214);
// PUG_DEBUG:214
 ?><?php
}); ?><?php
}; ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(485);
// PUG_DEBUG:485
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(460);
// PUG_DEBUG:460
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(459);
// PUG_DEBUG:459
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(245);
// PUG_DEBUG:245
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(243);
// PUG_DEBUG:243
 ?><?php // Cloning via http://stackoverflow.com/a/728694/885556
function clone(obj) {
    var copy;

    // Handle the 3 simple types, and null or undefined
    if (null == obj || "object" != typeof obj) return obj;

    // Handle Date
    if (obj instanceof Date) {
        copy = new Date();
        copy.setTime(obj.getTime());
        return copy;
    }

    // Handle Array
    if (obj instanceof Array) {
        copy = [];
        for (var i = 0, len = obj.length; i < len; i++) {
            copy[i] = clone(obj[i]);
        }
        return copy;
    }

    // Handle Object
    if (obj instanceof Object) {
        copy = {};
        for (var attr in obj) {
            if (obj.hasOwnProperty(attr)) copy[attr] = clone(obj[attr]);
        }
        return copy;
    }

    throw new Error("Unable to copy obj! Its type isn't supported.");
}
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(244);
// PUG_DEBUG:244
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(253);
// PUG_DEBUG:253
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(248);
// PUG_DEBUG:248
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(246);
// PUG_DEBUG:246
 ?><?php var get_bemto_tag_type = function(tagName) {
  var result = 'block'
  if (bemto_tag_metadata[tagName]) {
    result = bemto_tag_metadata[tagName].type || result;
  }
  return result;
}

var get_bemto_tag_content_type = function(tagName) {
  var result = 'block'
  if (bemto_tag_metadata[tagName]) {
    result = bemto_tag_metadata[tagName].content_type || bemto_tag_metadata[tagName].type || result;
  }
  return result;
}

var bemto_tag_metadata = {
  'hr': {
    'type': 'self_closing'
  },
  'br': {
    'type': 'self_closing'
  },
  'wbr': {
    'type': 'self_closing'
  },
  'source': {
    'type': 'self_closing'
  },
  'img': {
    'type': 'self_closing'
  },
  'input': {
    'type': 'self_closing'
  },
  'a': {
    'type': 'inline'
  },
  'abbr': {
    'type': 'inline'
  },
  'acronym': {
    'type': 'inline'
  },
  'b': {
    'type': 'inline'
  },
  'code': {
    'type': 'inline'
  },
  'em': {
    'type': 'inline'
  },
  'font': {
    'type': 'inline'
  },
  'i': {
    'type': 'inline'
  },
  'ins': {
    'type': 'inline'
  },
  'kbd': {
    'type': 'inline'
  },
  'map': {
    'type': 'inline'
  },
  'pre': {
    'type': 'inline'
  },
  'samp': {
    'type': 'inline'
  },
  'small': {
    'type': 'inline'
  },
  'span': {
    'type': 'inline'
  },
  'strong': {
    'type': 'inline'
  },
  'sub': {
    'type': 'inline'
  },
  'sup': {
    'type': 'inline'
  },
  'textarea': {
    'type': 'inline'
  },
  'time': {
    'type': 'inline'
  },
  'label': {
    'content_type': 'inline'
  },
  'p': {
    'content_type': 'inline'
  },
  'h1': {
    'content_type': 'inline'
  },
  'h2': {
    'content_type': 'inline'
  },
  'h3': {
    'content_type': 'inline'
  },
  'h4': {
    'content_type': 'inline'
  },
  'h5': {
    'content_type': 'inline'
  },
  'h6': {
    'content_type': 'inline'
  },
  'ul': {
    'content_type': 'list'
  },
  'ol': {
    'content_type': 'list'
  },
  'select': {
    'content_type': 'optionlist'
  },
  'datalist': {
    'content_type': 'optionlist'
  }
}
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(247);
// PUG_DEBUG:247
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(249);
// PUG_DEBUG:249
 ?><?php var default_bemto_settings = {
  'prefix': '',
  'element': '__',
  'modifier': '_',
  'default_tag': 'div',
  'nosrc_substitute': true,
  'flat_elements': true,
  'class_delimiter': ''
}

var bemto_output_settings = ['element', 'modifier'];

var bemto_settings = default_bemto_settings;

var get_bemto_settings = function() {
  var settings = clone(bemto_settings);
  if (bemto_settings_prefix      !== undefined) { settings['prefix']      = bemto_settings_prefix;      }
  if (bemto_settings_element     !== undefined) { settings['element']     = bemto_settings_element;     }
  if (bemto_settings_modifier    !== undefined) { settings['modifier']    = bemto_settings_modifier;    }
  if (bemto_settings_default_tag !== undefined) { settings['default_tag'] = bemto_settings_default_tag; }

  for (var i = 0; i < bemto_output_settings.length; i++) {
    var setting = bemto_output_settings[i];
    if (settings['output_' + setting] === undefined) {
      settings['output_' + setting] = settings[setting];
    }
  };

  return settings;
}

var set_bemto_setting = function(name, value) {
  bemto_settings[name] = value;

  //- Resetting the old variable-type setting
  if (name == 'prefix' && bemto_settings_prefix !== undefined) { bemto_settings_prefix = undefined; }
  if (name == 'element' && bemto_settings_element !== undefined) { bemto_settings_element = undefined; }
  if (name == 'modifier' && bemto_settings_modifier !== undefined) { bemto_settings_modifier = undefined; }
  if (name == 'default_tag' && bemto_settings_default_tag !== undefined) { bemto_settings_default_tag = undefined; }
}

var set_bemto_settings = function(settings) {
  for (var name in settings) {
    if (settings.hasOwnProperty(name)) {
      set_bemto_setting(name, settings[name]);
    }
  }
}
 ?><?php if (isset($__pug_mixins, $__pug_mixins['bemto_scope'])) {
    $__pug_save_3753019 = $__pug_mixins['bemto_scope'];
}
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['bemto_scope'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'settings', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(250);
// PUG_DEBUG:250
 ?><?php var old_bemto_settings = clone(bemto_settings) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(251);
// PUG_DEBUG:251
 ?><?php set_bemto_settings(settings) ?><?= (is_bool($_pug_temp = $__pug_children(get_defined_vars())) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(252);
// PUG_DEBUG:252
 ?><?php set_bemto_settings(old_bemto_settings) ?><?php
}; ?><?php if (isset($__pug_save_3753019)) {
    $__pug_mixins['bemto_scope'] = $__pug_save_3753019;
}
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(341);
// PUG_DEBUG:341
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(287);
// PUG_DEBUG:287
 ?><?php if (isset($__pug_mixins, $__pug_mixins['bemto_custom_inline_tag'])) {
    $__pug_save_9253088 = $__pug_mixins['bemto_custom_inline_tag'];
}
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['bemto_custom_inline_tag'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'customTag', null], [false, 'self_closing', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(254);
// PUG_DEBUG:254
 ?><?php self_closing = self_closing || false ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(255);
// PUG_DEBUG:255
 ?><<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(256);
// PUG_DEBUG:256
 ?><?= htmlspecialchars((is_bool($_pug_temp = customTag) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(264);
// PUG_DEBUG:264
 ?><?php if (attributes) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(263);
// PUG_DEBUG:263
 ?><?php for (var attribute in attributes) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(262);
// PUG_DEBUG:262
 ?><?php if (attributes.hasOwnProperty(attribute) && attributes[attribute] !== false && attributes[attribute] !== undefined) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(257);
// PUG_DEBUG:257
 ?> <?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(258);
// PUG_DEBUG:258
 ?><?= htmlspecialchars((is_bool($_pug_temp = attribute) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(259);
// PUG_DEBUG:259
 ?>="<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(260);
// PUG_DEBUG:260
 ?><?= (is_bool($_pug_temp = attributes[attribute] === true ? attribute : attributes[attribute]) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(261);
// PUG_DEBUG:261
 ?>"<?php } ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(266);
// PUG_DEBUG:266
 }  if (self_closing) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(265);
// PUG_DEBUG:265
 ?>/><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(267);
// PUG_DEBUG:267
 ?>><?= (is_bool($_pug_temp = $__pug_children(get_defined_vars())) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(268);
// PUG_DEBUG:268
 ?></<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(269);
// PUG_DEBUG:269
 ?><?= htmlspecialchars((is_bool($_pug_temp = customTag) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(270);
// PUG_DEBUG:270
 ?>><?php } ?><?php
}; ?><?php if (isset($__pug_mixins, $__pug_mixins['bemto_custom_tag'])) {
    $__pug_save_8301723 = $__pug_mixins['bemto_custom_tag'];
}
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['bemto_custom_tag'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'customTag', null], [false, 'tagMetadata', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(271);
// PUG_DEBUG:271
 ?><?php customTag = customTag || 'div' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(272);
// PUG_DEBUG:272
 ?><?php tagMetadata = tagMetadata || {} ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(273);
// PUG_DEBUG:273
 ?><?php var selfClosing = false ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(276);
// PUG_DEBUG:276
 ?><?php if (customTag.substr(-1) === '/') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(274);
// PUG_DEBUG:274
 ?><?php selfClosing = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(275);
// PUG_DEBUG:275
 ?><?php customTag = customTag.slice(0, -1) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(277);
// PUG_DEBUG:277
 }  var tag_type = tagMetadata.type || get_bemto_tag_type(customTag) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(286);
// PUG_DEBUG:286
 ?><?php switch (tag_type) { ?><?php case 'inline': ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'bemto_custom_inline_tag';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, array_merge([], attributes), [[false, customTag]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    ?><?= (is_bool($_pug_temp = $__pug_children(get_defined_vars())) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php
}); ?><?php break; ?><?php case 'self_closing': ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'bemto_custom_inline_tag';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](false, array_merge([], attributes), [[false, customTag], [false, true]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    ?><?php
}); ?><?php break; ?><?php default: ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(281);
// PUG_DEBUG:281
 ?><?php if (selfClosing) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(280);
// PUG_DEBUG:280
 ?><<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(278);
// PUG_DEBUG:278
 ?><?= (is_bool($_pug_temp = customTag) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(279);
// PUG_DEBUG:279
 ?><?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](attributes)) ? var_export($_pug_temp, true) : $_pug_temp) ?> /><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(285);
// PUG_DEBUG:285
 ?><<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(282);
// PUG_DEBUG:282
 ?><?= (is_bool($_pug_temp = customTag) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(283);
// PUG_DEBUG:283
 ?><?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](attributes)) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?= (is_bool($_pug_temp = $__pug_children(get_defined_vars())) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(284);
// PUG_DEBUG:284
 ?></<?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(282);
// PUG_DEBUG:282
 ?><?= (is_bool($_pug_temp = customTag) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?php } ?><?php } ?><?php
}; ?><?php if (isset($__pug_save_9253088)) {
    $__pug_mixins['bemto_custom_inline_tag'] = $__pug_save_9253088;
}
 if (isset($__pug_save_8301723)) {
    $__pug_mixins['bemto_custom_tag'] = $__pug_save_8301723;
}
 ?><?php if (isset($__pug_mixins, $__pug_mixins['bemto_tag'])) {
    $__pug_save_2925368 = $__pug_mixins['bemto_tag'];
}
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['bemto_tag'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'tag', null], [false, 'tagMetadata', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(288);
// PUG_DEBUG:288
 ?><?php var settings = get_bemto_settings() ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(289);
// PUG_DEBUG:289
 ?><?php tagMetadata = tagMetadata || {} ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(290);
// PUG_DEBUG:290
 ?><?php var newTag = tag || settings['default_tag'] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(291);
// PUG_DEBUG:291
 ?><?php var contextIndex = bemto_chain_contexts.length ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(298);
// PUG_DEBUG:298
 ?><?php if (!tag) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(293);
// PUG_DEBUG:293
 ?><?php if (bemto_chain_contexts[contextIndex-1] === 'inline') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(292);
// PUG_DEBUG:292
 ?><?php newTag = 'span' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(295);
// PUG_DEBUG:295
 }  elseif (bemto_chain_contexts[contextIndex-1] === 'list') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(294);
// PUG_DEBUG:294
 ?><?php newTag = 'li' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(297);
// PUG_DEBUG:297
 }  elseif (bemto_chain_contexts[contextIndex-1] === 'optionlist') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(296);
// PUG_DEBUG:296
 ?><?php newTag = 'option' ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(307);
// PUG_DEBUG:307
 }  if (!tag || tag == 'span' || tag == 'div') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(300);
// PUG_DEBUG:300
 ?><?php if (attributes.href) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(299);
// PUG_DEBUG:299
 ?><?php newTag = 'a' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(302);
// PUG_DEBUG:302
 }  if (attributes.for) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(301);
// PUG_DEBUG:301
 ?><?php newTag = 'label' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(304);
// PUG_DEBUG:304
 }  if (attributes.type) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(303);
// PUG_DEBUG:303
 ?><?php newTag = block ? 'button' : 'input' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(306);
// PUG_DEBUG:306
 }  elseif (attributes.src) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(305);
// PUG_DEBUG:305
 ?><?php newTag = 'img' ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(309);
// PUG_DEBUG:309
 }  if (bemto_chain_contexts[contextIndex-1] === 'list' && newTag !== 'li') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(308);
// PUG_DEBUG:308
 ?><li><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(312);
// PUG_DEBUG:312
 }  elseif (bemto_chain_contexts[contextIndex-1] !== 'list' && bemto_chain_contexts[contextIndex-1] !== 'pseudo-list' && newTag === 'li') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(310);
// PUG_DEBUG:310
 ?><ul><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(311);
// PUG_DEBUG:311
 ?><?php bemto_chain_contexts[bemto_chain_contexts.length] = 'pseudo-list' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(315);
// PUG_DEBUG:315
 }  elseif (bemto_chain_contexts[contextIndex-1] === 'pseudo-list' && newTag !== 'li') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(313);
// PUG_DEBUG:313
 ?></ul><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(314);
// PUG_DEBUG:314
 ?><?php bemto_chain_contexts = bemto_chain_contexts.splice(0,bemto_chain_contexts.length-1) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(316);
// PUG_DEBUG:316
 }  var content_type = tagMetadata.content_type || get_bemto_tag_content_type(newTag) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(317);
// PUG_DEBUG:317
 ?><?php bemto_chain_contexts[bemto_chain_contexts.length] = content_type ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(331);
// PUG_DEBUG:331
 ?><?php if (newTag == 'img') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(319);
// PUG_DEBUG:319
 ?><?php if (attributes.alt && !attributes.title) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(318);
// PUG_DEBUG:318
 ?><?php attributes.title = '' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(321);
// PUG_DEBUG:321
 }  if (attributes.title && !attributes.alt) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(320);
// PUG_DEBUG:320
 ?><?php attributes.alt = attributes.title ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(323);
// PUG_DEBUG:323
 }  if (!attributes.alt) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(322);
// PUG_DEBUG:322
 ?><?php attributes.alt = '' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(325);
// PUG_DEBUG:325
 }  if (attributes.alt === '') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(324);
// PUG_DEBUG:324
 ?><?php attributes.role = 'presentation' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(330);
// PUG_DEBUG:330
 }  if (!attributes.src) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(327);
// PUG_DEBUG:327
 ?><?php if (settings.nosrc_substitute === true) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(326);
// PUG_DEBUG:326
 ?><?php attributes.src = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(329);
// PUG_DEBUG:329
 }  elseif (settings.nosrc_substitute) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(328);
// PUG_DEBUG:328
 ?><?php attributes.src = settings.nosrc_substitute ?><?php } ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(334);
// PUG_DEBUG:334
 }  if (newTag == 'input') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(333);
// PUG_DEBUG:333
 ?><?php if (!attributes.type) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(332);
// PUG_DEBUG:332
 ?><?php attributes.type = "text" ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(337);
// PUG_DEBUG:337
 }  if (newTag == 'main') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(336);
// PUG_DEBUG:336
 ?><?php if (!attributes.role) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(335);
// PUG_DEBUG:335
 ?><?php attributes.role = 'main' ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(340);
// PUG_DEBUG:340
 }  if (newTag == 'html') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(338);
// PUG_DEBUG:338
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(339);
// PUG_DEBUG:339
 ?><!DOCTYPE html>

  +bemto_custom_tag(newTag, tagMetadata)&attributes(attributes)
    if block
      block

  //- Closing all the wrapper tails
  if bemto_chain_contexts[contextIndex-1] === 'list' && newTag != 'li'
    | </li>
<?php };
}; ?><?php if (isset($__pug_save_2925368)) {
    $__pug_mixins['bemto_tag'] = $__pug_save_2925368;
}
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(458);
// PUG_DEBUG:458
 ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(342);
// PUG_DEBUG:342
 ?><?php var bemto_chain = [] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(343);
// PUG_DEBUG:343
 ?><?php var bemto_chain_contexts = ['block'] ?><?php if (isset($__pug_mixins, $__pug_mixins['b'])) {
    $__pug_save_316960 = $__pug_mixins['b'];
}
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['b'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'options', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(344);
// PUG_DEBUG:344
 ?><?php var settings = get_bemto_settings() ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(346);
// PUG_DEBUG:346
 ?><?php if (options && options.prefix !== undefined) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(345);
// PUG_DEBUG:345
 ?><?php settings.prefix = options.prefix ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(347);
// PUG_DEBUG:347
 }  var tag = options && options.tag || ( typeof options == 'string' ? options : '') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(348);
// PUG_DEBUG:348
 ?><?php var isElement = options && options.isElement ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(349);
// PUG_DEBUG:349
 ?><?php var tagMetadata = options && options.metadata ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(350);
// PUG_DEBUG:350
 ?><?php var block_sets_context = false ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(448);
// PUG_DEBUG:448
 ?><?php if (attributes.class) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(351);
// PUG_DEBUG:351
 ?><?php var bemto_classes = attributes.class ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(353);
// PUG_DEBUG:353
 ?><?php if (bemto_classes instanceof Array) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(352);
// PUG_DEBUG:352
 ?><?php bemto_classes = bemto_classes.join(' ') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(354);
// PUG_DEBUG:354
 }  bemto_classes = bemto_classes.split(' ') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(355);
// PUG_DEBUG:355
 ?><?php var bemto_objects = [] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(356);
// PUG_DEBUG:356
 ?><?php var is_first_object = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(357);
// PUG_DEBUG:357
 ?><?php var new_context = [] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(421);
// PUG_DEBUG:421
 ?><?php foreach (bemto_classes as $i => $klass) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(358);
// PUG_DEBUG:358
 ?><?php var bemto_object = {} ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(359);
// PUG_DEBUG:359
 ?><?php var prev_object = bemto_objects[bemto_objects.length - 1] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(360);
// PUG_DEBUG:360
 ?><?php var sets_context = false ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(363);
// PUG_DEBUG:363
 ?><?php if (klass.match(/^[A-Z-]+[A-Z0-9-]?$/)) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(361);
// PUG_DEBUG:361
 ?><?php tag = klass.toLowerCase() ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(362);
// PUG_DEBUG:362
 ?><?php continue ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(365);
// PUG_DEBUG:365
 }  if (is_first_object && isElement) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(364);
// PUG_DEBUG:364
 ?><?php bemto_object['context'] = bemto_chain[bemto_chain.length - 1] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(366);
// PUG_DEBUG:366
 }  var modifier_class = klass.match(new RegExp('^(?!' + settings['element'] + '[A-Za-z0-9])' + settings['modifier'] + '(.+)$')) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(371);
// PUG_DEBUG:371
 ?><?php if (modifier_class && prev_object && prev_object.name) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(368);
// PUG_DEBUG:368
 ?><?php if (!prev_object['modifiers']) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(367);
// PUG_DEBUG:367
 ?><?php prev_object['modifiers'] = [] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(369);
// PUG_DEBUG:369
 }  prev_object.modifiers.push(modifier_class[1]) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(370);
// PUG_DEBUG:370
 ?><?php continue ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(372);
// PUG_DEBUG:372
 }  var element_class = klass.match(new RegExp('^(?!' + settings['modifier'] + '[A-Za-z0-9])' + settings['element'] + '(.+)$')) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(375);
// PUG_DEBUG:375
 ?><?php if (element_class) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(373);
// PUG_DEBUG:373
 ?><?php bemto_object['context'] = bemto_chain[bemto_chain.length - 1] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(374);
// PUG_DEBUG:374
 ?><?php klass = element_class[1] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(376);
// PUG_DEBUG:376
 }  var name_with_context = klass.match(new RegExp('^(.*[A-Za-z0-9])(?!' + settings['modifier'] + '$)' + settings['element'] + '$')) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(382);
// PUG_DEBUG:382
 ?><?php if (name_with_context) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(377);
// PUG_DEBUG:377
 ?><?php klass = name_with_context[1] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(378);
// PUG_DEBUG:378
 ?><?php bemto_object['is_context'] = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(379);
// PUG_DEBUG:379
 ?><?php sets_context = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(380);
// PUG_DEBUG:380
 ?><?php block_sets_context = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(381);
// PUG_DEBUG:381
 ?><?php isElement = false ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(383);
// PUG_DEBUG:383
 }  var name_with_modifier = klass.match(new RegExp('^(.*?[A-Za-z0-9])(?!' + settings['element'] + '[A-Za-z0-9])' + settings['modifier'] + '(.+)$')) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(388);
// PUG_DEBUG:388
 ?><?php if (name_with_modifier) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(384);
// PUG_DEBUG:384
 ?><?php klass = name_with_modifier[1] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(386);
// PUG_DEBUG:386
 ?><?php if (!bemto_object['modifiers']) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(385);
// PUG_DEBUG:385
 ?><?php bemto_object['modifiers'] = [] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(387);
// PUG_DEBUG:387
 }  bemto_object.modifiers.push(name_with_modifier[2]) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(389);
// PUG_DEBUG:389
 }  var found_prefix = '' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(390);
// PUG_DEBUG:390
 ?><?php var prefix_regex_string = '()?' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(409);
// PUG_DEBUG:409
 ?><?php if (settings.prefix) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(391);
// PUG_DEBUG:391
 ?><?php var prefix = settings.prefix ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(393);
// PUG_DEBUG:393
 ?><?php if (typeof prefix === 'string') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(392);
// PUG_DEBUG:392
 ?><?php prefix = { '': prefix } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(394);
// PUG_DEBUG:394
 }  var prefix_regex_test = [] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(401);
// PUG_DEBUG:401
 ?><?php if (prefix instanceof Object) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(399);
// PUG_DEBUG:399
 ?><?php foreach (prefix as $key => $value) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(396);
// PUG_DEBUG:396
 ?><?php if (typeof key === 'string' && key != '' && prefix_regex_test.indexOf(key) == -1) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(395);
// PUG_DEBUG:395
 ?><?php prefix_regex_test.push(key) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(398);
// PUG_DEBUG:398
 }  if (typeof value === 'string' && value != '' && prefix_regex_test.indexOf(value) == -1) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(397);
// PUG_DEBUG:397
 ?><?php prefix_regex_test.push(value) ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(400);
// PUG_DEBUG:400
 }  prefix_regex_string = '(' + prefix_regex_test.join('|') + ')?' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(402);
// PUG_DEBUG:402
 }  var name_with_prefix = klass.match(new RegExp('^' + prefix_regex_string + '([A-Za-z0-9]+.*)$')) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(408);
// PUG_DEBUG:408
 ?><?php if (name_with_prefix) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(403);
// PUG_DEBUG:403
 ?><?php klass = name_with_prefix[2] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(404);
// PUG_DEBUG:404
 ?><?php found_prefix = name_with_prefix[1] || '' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(405);
// PUG_DEBUG:405
 ?><?php found_prefix = prefix[found_prefix] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(407);
// PUG_DEBUG:407
 ?><?php if (found_prefix === undefined || found_prefix === true) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(406);
// PUG_DEBUG:406
 ?><?php found_prefix = name_with_prefix[1] ?><?php } ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(410);
// PUG_DEBUG:410
 }  bemto_object['prefix'] = (found_prefix || '').replace(/\-/g, '%DASH%').replace(/\_/g, '%UNDERSCORE%') ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(412);
// PUG_DEBUG:412
 ?><?php if (sets_context && klass.match(/^[a-zA-Z0-9]+.*/)) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(411);
// PUG_DEBUG:411
 ?><?php new_context.push(bemto_object.context ? (bemto_object.context + settings['element'] + klass) : (bemto_object.prefix + klass)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(413);
// PUG_DEBUG:413
 }  bemto_object['name'] = klass ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(414);
// PUG_DEBUG:414
 ?><?php is_first_object = false ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(419);
// PUG_DEBUG:419
 ?><?php if (bemto_object.context && bemto_object.context.length > 1) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(418);
// PUG_DEBUG:418
 ?><?php foreach (bemto_object.context as $i => $subcontext) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(415);
// PUG_DEBUG:415
 ?><?php var sub_object = clone(bemto_object) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(416);
// PUG_DEBUG:416
 ?><?php sub_object['context'] = [subcontext] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(417);
// PUG_DEBUG:417
 ?><?php bemto_objects.push(sub_object) ?><?php } ?><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(420);
// PUG_DEBUG:420
 ?><?php bemto_objects.push(bemto_object) ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(425);
// PUG_DEBUG:425
 }  if (!isElement && !new_context.length && bemto_objects[0] && bemto_objects[0].name && bemto_objects[0].name.match(/^[a-zA-Z0-9]+.*/)) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(422);
// PUG_DEBUG:422
 ?><?php bemto_objects[0]['is_context'] = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(423);
// PUG_DEBUG:423
 ?><?php new_context.push(bemto_objects[0].context ? (bemto_objects[0].context + settings['element'] + bemto_objects[0].name) : (bemto_objects[0].prefix + bemto_objects[0].name)) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(424);
// PUG_DEBUG:424
 ?><?php block_sets_context = true ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(432);
// PUG_DEBUG:432
 }  if (new_context.length) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(430);
// PUG_DEBUG:430
 ?><?php if (settings.flat_elements) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(429);
// PUG_DEBUG:429
 ?><?php foreach (new_context as $i => $subcontext) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(426);
// PUG_DEBUG:426
 ?><?php var context_with_element = subcontext.match(new RegExp('^(.*?[A-Za-z0-9])(?!' + settings['modifier'] + '[A-Za-z0-9])' + settings['element'] + '.+$')) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(428);
// PUG_DEBUG:428
 ?><?php if (context_with_element) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(427);
// PUG_DEBUG:427
 ?><?php new_context[i] = context_with_element[1] ?><?php } ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(431);
// PUG_DEBUG:431
 }  bemto_chain[bemto_chain.length] = new_context ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(446);
// PUG_DEBUG:446
 }  if (bemto_objects.length) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(433);
// PUG_DEBUG:433
 ?><?php var new_classes = [] ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(442);
// PUG_DEBUG:442
 ?><?php foreach (bemto_objects as $bemto_object) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(441);
// PUG_DEBUG:441
 ?><?php if (bemto_object.name) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(434);
// PUG_DEBUG:434
 ?><?php var start = bemto_object.prefix ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(436);
// PUG_DEBUG:436
 ?><?php if (bemto_object.context) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(435);
// PUG_DEBUG:435
 ?><?php start = bemto_object.context + settings.output_element ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(437);
// PUG_DEBUG:437
 }  new_classes.push(start + bemto_object.name) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(440);
// PUG_DEBUG:440
 ?><?php if (bemto_object.modifiers) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(439);
// PUG_DEBUG:439
 ?><?php foreach (bemto_object.modifiers as $modifier) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(438);
// PUG_DEBUG:438
 ?><?php new_classes.push(start + bemto_object.name + settings.output_modifier + modifier) ?><?php } ?><?php } ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(443);
// PUG_DEBUG:443
 }  var delimiter = settings.class_delimiter ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(444);
// PUG_DEBUG:444
 ?><?php delimiter = delimiter ? (' ' + delimiter + ' ') : ' ' ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(445);
// PUG_DEBUG:445
 ?><?php attributes.class = new_classes.join(delimiter).replace(/%DASH%/g, '-').replace(/%UNDERSCORE%/g, '_') ?><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(447);
// PUG_DEBUG:447
 ?><?php attributes.class = undefined ?><?php } ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(449);
// PUG_DEBUG:449
 }  if (block) { ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'bemto_tag';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, array_merge([], attributes), [[false, tag], [false, tagMetadata]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    ?><?= (is_bool($_pug_temp = $__pug_children(get_defined_vars())) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php
}); ?><?php } else { ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'bemto_tag';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](false, array_merge([], attributes), [[false, tag], [false, tagMetadata]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    ?><?php
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(451);
// PUG_DEBUG:451
 }  if (!isElement && block_sets_context) { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(450);
// PUG_DEBUG:450
 ?><?php bemto_chain = bemto_chain.splice(0,bemto_chain.length-1) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(452);
// PUG_DEBUG:452
 }  bemto_chain_contexts = bemto_chain_contexts.splice(0,bemto_chain_contexts.length-1) ?><?php
}; ?><?php if (isset($__pug_mixins, $__pug_mixins['e'])) {
    $__pug_save_6087302 = $__pug_mixins['e'];
}
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['e'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'options', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(454);
// PUG_DEBUG:454
 ?><?php if (options && typeof options == 'string') { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(453);
// PUG_DEBUG:453
 ?><?php options = { 'tag': options } ?><?php } else { ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(455);
// PUG_DEBUG:455
 ?><?php options = options || {} ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(456);
// PUG_DEBUG:456
 }  options['isElement'] = true ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, array_merge([], attributes), [[false, options]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    ?><?= (is_bool($_pug_temp = $__pug_children(get_defined_vars())) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(457);
// PUG_DEBUG:457
 ?><?php
}); ?><?php
}; ?><?php if (isset($__pug_save_316960)) {
    $__pug_mixins['b'] = $__pug_save_316960;
}
 if (isset($__pug_save_6087302)) {
    $__pug_mixins['e'] = $__pug_save_6087302;
}
 ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'foo']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(461);
// PUG_DEBUG:461
 ?>bar<?php
}); ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'foo']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'bar']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(462);
// PUG_DEBUG:462
 ?>baz<?php
});;
}); ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'foo'], ['class' => 'bar__']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](false, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'baz']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    ?><?php
});;
}); ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'foo__'], ['class' => 'bar__']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](false, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'baz_mod']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    ?><?php
});;
}); ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'block_foo']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(463);
// PUG_DEBUG:463
 ?>bar<?php
}); ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'H1'], ['class' => 'title']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(464);
// PUG_DEBUG:464
 ?>header<?php
}); ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](false, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'INPUT'], ['class' => 'input']), [[false, required]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    ?><?php
}); ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'MAIN'], ['class' => 'content']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(465);
// PUG_DEBUG:465
 ?>CONTENT<?php
}); ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'TEXTAREA']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(466);
// PUG_DEBUG:466
 ?>Oh hello<?php
}); ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'PRE']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(467);
// PUG_DEBUG:467
 ?>Oh hello<?php
}); ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'block_foo'], ['class' => '_bar'], ['class' => '_baz']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'element_type_lol'], ['class' => '_mode_moddy']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(468);
// PUG_DEBUG:468
 ?>Blah<?php
});;
}); ?><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(469);
// PUG_DEBUG:469
 ?><!--  Tag as first uppercase class --><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(471);
// PUG_DEBUG:471
 ?><p><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'SPAN'], ['class' => 'foo']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(470);
// PUG_DEBUG:470
 ?>bar<?php
}); ?></p><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(472);
// PUG_DEBUG:472
 ?><!--  Tag as option --><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(474);
// PUG_DEBUG:474
 ?><p><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'foo']), [[false, {tag: 'span'}]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(473);
// PUG_DEBUG:473
 ?>baz<?php
}); ?></p><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(475);
// PUG_DEBUG:475
 ?><!--  Backwards compatible way --><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(477);
// PUG_DEBUG:477
 ?><p><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'foo']), [[false, 'span']], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'bar']), [[false, 'span']], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(476);
// PUG_DEBUG:476
 ?>raz<?php
});;
}); ?></p><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(478);
// PUG_DEBUG:478
 ?><!--  Self-closing tag as option --><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(480);
// PUG_DEBUG:480
 ?><p><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'foo']), [[false, {tag: 'closey/'}]], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(479);
// PUG_DEBUG:479
 ?>baz<?php
}); ?></p><?php 
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(481);
// PUG_DEBUG:481
 ?><!--  Empty block and empty element --><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, [], [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, [], [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(482);
// PUG_DEBUG:482
 ?>foo<?php
}); ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'bar_mod']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(483);
// PUG_DEBUG:483
 ?>foo<?php
});;
}); ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'FOO']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'BAR']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    
\Phug\Renderer\Profiler\ProfilerModule::recordProfilerDisplayEvent(484);
// PUG_DEBUG:484
 ?>foo<?php
});;
}); ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => '_']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](false, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'lol']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    ?><?php
});;
}); ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](true, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => '_mod']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'e';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](false, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'lol']), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    ?><?php
});;
}); ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixin_vars = [];
foreach (array_keys(get_defined_vars()) as $key) {
    if (mb_substr($key, 0, 6) === '__pug_' || in_array($key, ['attributes', 'block'])) {
        continue;
    }
    $ref = &$GLOBALS[$key];
    $value = &$$key;
    if($ref !== $value){
        $__pug_mixin_vars[$key] = &$value;
        continue;
    }
    $savedValue = $value;
    $value = ($value === true) ? false : true;
    $isGlobalReference = ($value === $ref);
    $value = $savedValue;
    if (!$isGlobalReference) {
        $__pug_mixin_vars[$key] = &$value;
    }
}
if (!isset($__pug_children)) {
    $__pug_children = null;
}
$__pug_mixin_name = 'b';
if (!isset($__pug_mixins[$__pug_mixin_name])) {
    throw new \InvalidArgumentException("Unknown $__pug_mixin_name mixin called.");
}

$__pug_mixins[$__pug_mixin_name](false, $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'](['class' => 'foo'], ['class' => 'SPAN'], ['bar' => false]), [], $__pug_mixin_vars, function ($__pug_children_vars) use (&$__pug_mixins, $__pug_children, &$pugModule) {
    foreach (array_keys($__pug_children_vars) as $key) {
        if (mb_substr($key, 0, 6) === '__pug_') {
            continue;
        }
        $ref = &$GLOBALS[$key];
        $value = &$__pug_children_vars[$key];
        if($ref !== $value){
            $$key = &$value;
            continue;
        }
    }
    ?><?php
}); ?>