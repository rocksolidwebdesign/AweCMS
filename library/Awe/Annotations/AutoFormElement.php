<?php
/*
 * AweCMS
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt
 *
 * It is also available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 */

namespace Awe\Annotations;

use Doctrine\Common\Annotations\Annotation;

final class AutoFormElement extends Annotation
{
    public $type;
    public $options;
    public $label;
    public $validators;
    public $display_column;
    public $name;
    public $edit_inline;
    public $compact_view;
    public $add_method;
    public $clear_method;
    public $params;
    public $no_list;
    public $list_label;
}
