<?php

return [
    'accepted' => 'Trường :attribute phải được chấp nhận.',
    'array' => 'Trường :attribute phải là một mảng.',
    'boolean' => 'Trường :attribute phải có giá trị đúng hoặc sai.',
    'date' => 'Trường :attribute phải là một ngày hợp lệ.',
    'distinct' => 'Trường :attribute có giá trị bị trùng lặp.',
    'email' => 'Trường :attribute phải là một địa chỉ email hợp lệ.',
    'exists' => 'Giá trị đã chọn của :attribute không hợp lệ.',
    'file' => 'Trường :attribute phải là một tệp.',
    'image' => 'Trường :attribute phải là hình ảnh.',
    'in' => 'Giá trị đã chọn của :attribute không hợp lệ.',
    'integer' => 'Trường :attribute phải là số nguyên.',
    'max' => [
        'array' => 'Trường :attribute không được có nhiều hơn :max phần tử.',
        'file' => 'Tệp :attribute không được lớn hơn :max KB.',
        'numeric' => 'Trường :attribute không được lớn hơn :max.',
        'string' => 'Trường :attribute không được dài hơn :max ký tự.',
    ],
    'min' => [
        'array' => 'Trường :attribute phải có ít nhất :min phần tử.',
        'file' => 'Tệp :attribute phải có dung lượng ít nhất :min KB.',
        'numeric' => 'Trường :attribute phải có giá trị tối thiểu :min.',
        'string' => 'Trường :attribute phải có ít nhất :min ký tự.',
    ],
    'mimes' => 'Tệp :attribute phải có định dạng: :values.',
    'regex' => 'Định dạng của trường :attribute không hợp lệ.',
    'required' => 'Vui lòng nhập :attribute.',
    'string' => 'Trường :attribute phải là chuỗi ký tự.',
    'unique' => 'Giá trị :attribute đã tồn tại.',
    'url' => 'Trường :attribute phải là một URL hợp lệ.',

    'custom' => [
        'featured_image' => [
            'required' => 'Vui lòng chọn ảnh đại diện.',
        ],
    ],

    'attributes' => [
        'featured_image' => 'ảnh đại diện',
        'post_category_id' => 'chuyên mục',
        'enabled_locales' => 'ngôn ngữ bài viết',
        'title' => 'tiêu đề',
        'slug' => 'đường dẫn thân thiện',
        'sort_order' => 'thứ tự hiển thị',
    ],
];
