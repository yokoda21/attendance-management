<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'attendance_id' => ['required', 'exists:attendances,id'],
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i', 'after:clock_in'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end' => ['nullable', 'date_format:H:i', 'after:breaks.*.break_start'],
            'note' => ['required', 'string'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_in.date_format' => '出勤時間の形式が正しくありません',
            'clock_out.required' => '退勤時間を入力してください',
            'clock_out.date_format' => '退勤時間の形式が正しくありません',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.break_start.date_format' => '休憩時間の形式が正しくありません',
            'breaks.*.break_end.date_format' => '休憩時間の形式が正しくありません',
            'breaks.*.break_end.after' => '休憩時間が不適切な値です',
            'note.required' => '備考を記入してください',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockOut = $this->input('clock_out');
            $breaks = $this->input('breaks', []);

            if ($clockOut && $breaks) {
                foreach ($breaks as $index => $break) {
                    // 休憩開始が退勤時間より後の場合
                    if (isset($break['break_start']) && $break['break_start'] > $clockOut) {
                        $validator->errors()->add(
                            "breaks.{$index}.break_start",
                            '休憩時間が不適切な値です'
                        );
                    }

                    // 休憩終了が退勤時間より後の場合
                    if (isset($break['break_end']) && $break['break_end'] > $clockOut) {
                        $validator->errors()->add(
                            "breaks.{$index}.break_end",
                            '休憩時間もしくは退勤時間が不適切な値です'
                        );
                    }
                }
            }
        });
    }
}
