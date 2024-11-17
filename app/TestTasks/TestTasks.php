<?php

namespace App\TestTasks;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestTasks{

    static public function initTables(){
        
        /*
        id
        responsible_user_id (ответственный) 
        creator_user_id (постановщик) 
        complete_at (дата окончания) 
        created_at (дата создания) 
        title (название задачи) 
        description (описание задачи)
        */
        if ( !Schema::hasTable('tasks')) 
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->string('responsible_user_id');
                $table->string('creator_user_id');
                $table->timestamp('complete_at');
                $table->timestamp('created_at');
                $table->string('title');
                $table->string('description');
            });

    }

    static private function getToArray( $object ){
        $array = [];
        foreach( $object as $item )
            $array[] = (array)$item;

        return $array;
    }

    static public function getUsers( Request $request ){
        $output = self::getToArray(  DB::table('users')->select('*')->get() );

        return self::output($output);
    }

    static private function output( $output ){
        return response()->json($output); 
    }

    static private function isAuthor( $id ){
        return Auth::id() && count( DB::table('tasks')->select('creator_user_id')->where('creator_user_id', Auth::id())->where('id', $id)->get() );
    }

    static public function tasks(Request $request, $id = null){ 
        self::initTables();

        $output = [];
        $perPage = 10;
        $method = $request->method();

        if( $method === 'GET' ){
           
            $fields = $request->all();

        }else{
            $fields = [
                'responsible_user_id' => '',
                'creator_user_id' => Auth::id(),
                'complete_at' => '1971-00-00 00:00:00',
                'created_at' => Auth::id() ? now()->toDateTimeString() : '',
                'title' => '',
                'description' => '',
            ];

            $fields = array_merge( $fields, $request->all() );
        }

        if( $method === 'GET' ){/// получение списка всех задач / получение деталей задачи по ее идентификатору

            $tasks = DB::table('tasks')->select('*');
            if( $id ){
                $tasks = $tasks->where('id', $id);
            }else{
                foreach( $fields as $key => $field ){
                    if( trim( !$field ) || $key === 'page' ) continue;

                    if( $key === 'complete_range' || $key === 'created_range' ){
                        $key = $key === 'complete_range' ? 'complete_at' : 'created_at';
                        $range = explode( ' -- ', $field );
                        $tasks = $tasks->where($key, '>' , $range[0]);
                        if( isset( $range[1] ) ){
                            $tasks = $tasks->where($key, '<' , $range[1]);
                        }

                    }elseif( $key === 'creator_user_id' || $key === 'responsible_user_id' )
                        $tasks = $tasks->where($key, $field);
                    else
                        $tasks = $tasks->where($key, 'like', '%'. $field .'%');
                }
            }

            if( $id ){
                $tasks = $tasks->get();
                $output['data'] = self::getToArray( $tasks );

            }else{
                $tasks = $tasks->paginate( $perPage );
                $output['data'] = self::getToArray( $tasks->items() );
                $output['paginator'] = $tasks;
            }

            $users = [];
            foreach( DB::table('users')->select('*')->get() as $user ){
                $users[ $user->id ] = (array)$user;
            }

            $dateFormatter = new \IntlDateFormatter('ru_RU');
            $dateFormatter->setPattern('d MMMM yyyy');

            foreach( $output['data'] as &$item ){
                if( isset( $users[ $item['creator_user_id'] ] ) ){
                    $item['creator_user'] = $users[ $item['creator_user_id'] ]['name'];
                }

                if( isset( $users[ $item['responsible_user_id'] ] ) ){
                    $item['responsible_user'] = $users[ $item['responsible_user_id'] ]['name'];
                }

                if( (int)$item['creator_user_id'] === Auth::id() ) 
                    $item['canEdit'] = true;
                
                if( !$id || !($item['canEdit']??false) )
                    $item['complete_at'] = $dateFormatter->format( strtotime( $item['complete_at'] ) );

                $item['created_at'] = $dateFormatter->format( strtotime( $item['created_at'] ) );

            }

            unset( $item );
/*
            if( !$id ){
                foreach( $output['data'] as &$item ){
                    $item['complete_at'] = $dateFormatter->format( strtotime( $item['complete_at'] ) );
                }
                unset( $item );
            }
*/

        }elseif( $method === 'POST' ){/// создание новой задачи

            if( !Auth::id() ){
                $output['success'] = false;
                $output['errors'] = ['Вы не авторизованы'];
            }elseif( count( array_filter( $fields ) ) !== count( $fields ) ){
                $output['success'] = false;
                $output['errors'] = ['Все поля должны быть заполнены'];
            }else{
                $id = DB::table('tasks')->insertGetId([
                    'title' => $fields['title'],
                    'description' => $fields['description'],
                    'responsible_user_id' => $fields['responsible_user_id'],
                    'complete_at' => $fields['complete_at'],
                    'creator_user_id' => $fields['creator_user_id'],
                    'created_at' => $fields['created_at']
                ]);

                $output['success'] = (bool)$id;
            }
            
        }elseif( $method === 'PATCH' ){/// обновление задачи по ее идентификатору
            
            if( !( $id && self::isAuthor($id) ) ){
                $output['success'] = false;
                $output['errors'] = ['Вы не постановщик задачи'];
            }elseif( count( array_filter( $fields ) ) !== count( $fields ) ){
                $output['success'] = false;
                $output['errors'] = ['Все поля должны быть заполнены'];
            }else{
                DB::table('tasks')->where('id', $id)->update([
                    'title' => $fields['title'],
                    'description' => $fields['description'],
                    'responsible_user_id' => $fields['responsible_user_id'],
                    'complete_at' => $fields['complete_at'],
                ]);

                $output['success'] = (bool)$id;
            }

        }elseif( $method === 'DELETE' ){/// удаление задачи по ее идентификатору
            if( $id && self::isAuthor($id) ){
                DB::table('tasks')->where('creator_user_id', Auth::id())->where('id', $id)->delete();
                $output['success'] = true;
            }else
                $output['success'] = false;
        }

        
        return self::output( $output );

    }


    static private function hasEmail( Request $request ){
        
        $hasEmail = DB::table('users')->select('*')->where('email', $request->all()['email']);
        return (bool)count($hasEmail->get());
        
    }

    static private function validateFields( Request $request, array $addFields = [] ): array{

        $messages = [
            'required' => 'Поле :attribute должно быть заполнено',
            'email' => 'Поле :attribute должно быть электронной почтой',
        ];

        $validator = \Validator::make($request->all(), array_merge( [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], $addFields ), $messages);

        return $validator->errors()->all();
    }

    static public function login(Request $request, $auth = false){
        self::initTables();

        $fields = $request->all();

        $output['FIELDS'] = $fields;

        $validated = self::validateFields($request);

        if( !count($validated) ){
            $hasUser = DB::table('users')->select('id', 'name')->where('email', $fields['email'])->where('password', md5($fields['password']))->get();
            if( count($hasUser) ){
                if( Auth::id() ){
                    $request->user()->tokens()->delete();
                }
                Auth::loginUsingId( $hasUser[0]->id );
                $request->user()->tokens()->delete();
                $output['auth']['token'] = $request->user()->createToken('token-name')->plainTextToken;
                $output['auth']['name'] = $hasUser[0]->name;
            }else{
                $output['errors'] = ['Пароль или email не верные'];
            }
        }else{
            $output['errors'] = $validated;
        }

        return self::output( $output );
    }

    static public function registration(Request $request){
        self::initTables();

        $fields = $request->all();
        $output['FIELDS'] = $fields;

        $validated = self::validateFields($request, ['name' => ['required']]);

        if( $validated ){
            $output['errors'] = $validated;
        }elseif( self::hasEmail($request) === true ){
            $output['errors'] = 'Пользователь с таким email существует';
        }else{
            $id = DB::table('users')->insertGetId([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => md5( $fields['password'] )
            ]);
            
            if( $id ){
                Auth::loginUsingId( $id );
                $output['auth']['token'] = $request->user()->createToken('token-name')->plainTextToken;
                $output['auth']['name'] = DB::table('users')->select('name')->where('id', $id)->get()[0]->name;
            }
        }

        return self::output( $output );
    }

    static public function logout(Request $request){
        
        $request->user()?->tokens()->delete();
        Auth::logout();
        $output['success'] = true;

        return self::output( $output );
    }

}