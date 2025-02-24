<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PicbookPage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'picbook_id',
        'page_number',
        'image_url',
        'none_skin',
        'elements',
        'is_choices',
        'question',
        'status',
        'is_ai_face',
        'mask_image_url',
        'has_replaceable_text',
        'text_elements',
        'character_sequence'
    ];

    protected $casts = [
        'elements' => 'array',
        'text_elements' => 'array',
        'is_choices' => 'boolean',
        'is_ai_face' => 'boolean',
        'has_replaceable_text' => 'boolean',
        'character_sequence' => 'array'
    ];

    public function picbook()
    {
        return $this->belongsTo(Picbook::class);
    }

    public function variants()
    {
        return $this->hasMany(PicbookPageVariant::class, 'page_id');
    }

    /**
     * 获取指定肤色组合的角色蒙版
     * @param array $skincolors 每个角色的肤色，如 [2, 1] 表示角色1用黄色，角色2用白色
     * @param string $language 语言
     * @param int $gender 性别
     * @return array 返回按位置顺序排列的蒙版图片URL数组
     */
    public function getCharacterMasks($skincolors, $language, $gender = 1)
    {
        if (count($skincolors) !== count($this->character_sequence)) {
            throw new \Exception('肤色数量与角色数量不匹配');
        }

        // 获取所需的所有变体
        $variants = [];
        foreach (array_unique($skincolors) as $skincolor) {
            $variants[$skincolor] = $this->variants()
                ->where([
                    'language' => $language,
                    'gender' => $gender,
                    'skincolor' => $skincolor
                ])->first();
            
            if (!$variants[$skincolor]) {
                throw new \Exception("找不到肤色 {$skincolor} 的变体");
            }
        }

        // 根据角色序列和指定的肤色组合获取对应的蒙版
        $masks = [];
        foreach ($this->character_sequence as $position => $character_id) {
            $skincolor = $skincolors[$character_id - 1]; // 获取该角色应该使用的肤色
            $masks[] = $variants[$skincolor]->getCharacterMask($position);
        }

        return $masks;
    }
} 