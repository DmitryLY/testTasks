<script setup lang="ts">
import { ref } from 'vue'

import store from "./../js/store";

init();
setTaskValue();
getUsers();
getTask();

import { onBeforeRouteUpdate } from 'vue-router'
onBeforeRouteUpdate( init );

</script>

<template>
    <div class="task">
        <div v-if="task?.id">
            <div class="label">Постановщик</div>
            <div>{{ creator_user }}</div>
        </div>

        <div v-if="task?.id">
            <div class="label">Дата создания</div>
            <div>{{ created_at }}</div>
        </div>

        <div>
            <label><div class="label">Ответственный</div>
                <select v-if="!task?.id || ( store.state.auth && task?.canEdit )" type="text" name="responsible_user_id" v-model="responsible_user_id">
                    <option v-for="user in users" :value="user.id" :key="user.id">
                        {{ user.name }}
                    </option>
                </select>
                <div v-else>{{ responsible_user }}</div>

            </label>
        </div>
        <div>
            <label><div class="label">Дата окончания</div>
                <input v-if="!task?.id || ( store.state.auth && task?.canEdit )" type="datetime-local" name="complete_at" v-model="complete_at">
                <div v-else>{{ complete_at }}</div>
            </label>
        </div>
        <div>
            <label><div class="label">Название</div>
                <input v-if="!task?.id || ( store.state.auth && task?.canEdit )"  type="text" name="title" v-model="title">
                <div v-else>{{ title }}</div>
            </label>
        </div>
        <div>
            <label><div class="label">Описание</div>
                <textarea v-if="!task?.id || ( store.state.auth && task?.canEdit )" name="description" v-model="description"></textarea>
                <div v-else>{{ description }}</div>
            </label>
        </div>
    </div>

    <div v-if="errors?.length" >
        <div v-for="error in errors">
            {{ error }}
        </div>
    </div>

    <template v-if="store.state.auth">

        <button v-if="!task?.id" @click="sendTask()">
            Добавить
        </button>
        <button v-if="task?.id && task?.canEdit" @click="sendTask()">
            Редактировать
        </button>
        <button v-if="task?.id && task?.canEdit" @click="sendTask(true)">
            Удалить
        </button>

    </template>

</template>

<script lang="ts">
    import { useRoute } from 'vue-router'

    var task = ref({id: undefined, canEdit: undefined});
    var errors = ref([]);

    function init(){
        task.value.id =  undefined;
        title.value = '';
        description.value = '';
        responsible_user_id.value = '';
        complete_at.value = '';
        task.value.canEdit = undefined;
        errors.value.splice( 0 )
    }

    function setTaskValue(){
        task.value.id = useRoute().params.id;
    }

    var creator_user = ref(''), created_at = ref(''), responsible_user = ref(''), responsible_user_id = ref(''), complete_at = ref(''), title = ref(''), description = ref('');
    var users = ref([]);

    async function getUsers(){
        
        var users_db = await fetch('/public/api/users', {
            method: 'POST',
            credentials: "same-origin",
            headers: {
                "X-CSRF-TOKEN": store.state.CSRF,
                "Authorization": `Bearer ${store.state.auth?.token}`
            }
        }).then(function(response) {
            return response.json();
        });

        users.value.splice( 0 );
        users.value = users.value.concat( users_db );
    }

    async function sendTask(remove){
        

        var method = 'POST';
        if( task?.value.id )
            method = remove ? 'DELETE' : 'PATCH';

        var headers = {
            "X-CSRF-TOKEN": store.state.CSRF,
            "Authorization": `Bearer ${store.state.auth?.token}`
        };
        if( method === 'POST' ){
            var sendData = new FormData;
            sendData.append('responsible_user_id', responsible_user_id.value);
            sendData.append('complete_at', complete_at.value);
            sendData.append('title', title.value);
            sendData.append('description', description.value);
        }else{
            sendData = new URLSearchParams(
                {
                    responsible_user_id: responsible_user_id.value,
                    complete_at: complete_at.value,
                    title: title.value,
                    description: description.value,
                }
            ).toString();
            headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        var json = await fetch('/public/api/tasks' + ( task?.value.id ? '/'+task?.value.id : '' ) , {
            method: method,
            credentials: "same-origin",
            body: sendData,
            headers
        }).then(function(response) {
            return response.json();
        });

        if( json.success )
            window.dispatchEvent( new CustomEvent( 'toMain' ) );
        else
            errors.value.splice( 0 ), ( errors.value = errors.value.concat( json.errors ) );

    }

    async function getTask(){
        if( !task.value.id )
            return;

        var json = await fetch('/public/api/tasks/' + task.value.id, {
            method: 'GET',
            credentials: "same-origin",
            headers: {
                "X-CSRF-TOKEN": store.state.CSRF,
                "Authorization": `Bearer ${store.state.auth?.token}`
            }
        }).then(function(response) {
            return response.json();
        });

        var task_json = json?.data?.[0];

        if( !task_json )
            return;

        creator_user.value = task_json.creator_user;
        created_at.value = task_json.created_at;
        responsible_user.value = task_json.responsible_user;
        title.value = task_json.title;
        description.value = task_json.description;
        responsible_user_id.value = task_json.responsible_user_id;
        complete_at.value = task_json.complete_at;
        task.value.canEdit = task_json.canEdit;

    }

</script>

<style>

    .task .label{
        margin-top: 10px;
        margin-bottom: 5px;
    }

    .task > div{
        margin-bottom: 20px;
    }

    .task input, .task textarea, .task select{
        width: 100%;
        max-width: 350px;
    }

    .task textarea{
        height: 150px;
    }

</style>