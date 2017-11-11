<?php  
namespace app\common\taglib;

use think\template\TagLib;

/**
 * TAG标签库解析类
 * @category   App
 * @package  App
 * @subpackage  Common.Taglib
 * @author    heihei <8540325@qq.com>
 */
class Tag extends Taglib
{

	// 标签定义
    protected $tags = [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'auth'         => ['attr' => 'name', 'expression' => true],
        'elseauth'     => ['attr' => 'name', 'close' => 0, 'expression' => true],
    ];

    /**
     * auth标签解析
     * 格式：
     * {auth name=""}
     * {/auth}
     * @access public
     * @param array $tag 标签属性
     * @param string $content 标签内容
     * @return string
     */
    public function tagAuth($tag, $content)
    {
        $name = !empty($tag['expression']) ? $tag['expression'] : $tag['name'];
        $auth = $this->getAuth();
        $auth = $this->parseArr2Str($auth);
        $parseStr  = "<?php if(in_array('" . $name . "', ".$auth .'  )): ?>' . $content . '<?php endif; ?>';
        return $parseStr;
    }

    /**
     * elseauth标签解析
     * 格式：见if标签
     * @access public
     * @param array $tag 标签属性
     * @param string $content 标签内容
     * @return string
     */
    public function tagElseauth($tag, $content)
    {
        $name = !empty($tag['expression']) ? $tag['expression'] : $tag['name'];
        $auth = $this->getAuth();
        $auth = $this->parseArr2Str($auth);
        $parseStr  = "<?php elseif(in_array('" . $name ."', ".$auth. ')): ?>';
        return $parseStr;
    }

    /**
    * 判断是否有权限，不同实现方式则需重写
    * @param str $name
    * @return array
    */
    protected function getAuth()
    {
    	$admin = session('admin');
    	return ['a','b','c'];
    }

    protected function parseArr2Str($arr)
    {
    	$val = array_values($arr);
    	$str = '[';
    	foreach($val as $v){
    		$str = $str . "'". $v ."'" .', ' ;
    	}
    	$str .= ']';
    	return $str;
    }
}