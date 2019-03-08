<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

use App\Models\AdminsGroups;
use App\Models\Answer;
use App\Models\Categories;
use App\Models\Courses;
use App\Models\Documents;
use App\Models\Groups;
use App\Models\Highlights;
use App\Models\Instructors;
use App\Models\QA;
use App\Models\Questions;
use App\Models\Quiz;
use App\Models\Slides;
use App\Models\Topics;
use App\Models\Transcodings;
use App\Models\Videos;

use Input;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /* ========== Sortable ========== */
        AdminsGroups::deleting(function ($model) {
            $model->next()->decrement('order');
        });
        Answer::deleting(function ($model) {
            $model->next()->decrement('order');
        });
        Categories::deleting(function ($model) {
            $model->next()->decrement('order');
        });
        Courses::deleting(function ($model) {
            $model->next()->decrement('order');
        });
        Documents::deleting(function ($model) {
            $model->next()->decrement('order');
        });
        Groups::deleting(function ($model) {
            $model->next()->decrement('order');
        });
        Highlights::deleting(function ($model) {
            $model->next()->decrement('order');
        });
        Instructors::deleting(function ($model) {
            $model->next()->decrement('order');
        });
        QA::deleting(function ($model) {
            $model->next()->decrement('order');
        });
        Questions::deleting(function ($model) {
            $model->next()->decrement('order');
        });
        Quiz::deleting(function ($model) {
            $model->next()->decrement('order');
        });
        Slides::deleting(function ($model) {
            $model->next()->decrement('order');
        });
        Topics::deleting(function ($model) {
            $model->next()->decrement('order');
        });
        Transcodings::deleting(function ($model) {
            $model->next()->decrement('order');
        });
        Videos::deleting(function ($model) {
            $model->next()->decrement('order');
        });
        /* ========== Sortable ========== */


        /* ========== Custom Validator ========== */
        Validator::extend('not_contain_credentials', function ($attribute, $value, $parameters, $validator) {
            foreach ($parameters as $param) {
                $sanitizeParam = explode("@", $param)[0];
                if (stripos($value, $sanitizeParam) !== false) {
                    return false;
                }
            }

            return true;
        });

        Validator::extend('required_if_greater_than_or_equal', function($attribute, $value, $parameters, $validator) {
            dd($validator);
            $data = $validator->getData();

            if (isset($data[$parameters[0]]) && isset($parameters[1])) {
                $other = $data[$parameters[0]];
                $check = $parameters[1];

                if (intval($other) >= intval($check)) {
                    return $validator->Required($attribute, $value);
                }
            }

            return true;
        });
        /* ========== Custom Validator ========== */
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
