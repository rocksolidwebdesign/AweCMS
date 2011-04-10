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
    public $displayColumn;
    public $name;
    public $editInline;
    public $compactView;
    public $addMethod;
    public $clearMethod;
    public $params;
    public $noList;
    public $listLabel;
}
