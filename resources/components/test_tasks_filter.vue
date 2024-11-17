<script setup lang="ts">

    import { ref } from 'vue';
    import store from './../js/store';

    defineProps(['getTasks', 'showFilter']);
    
    getUsers();
    
</script>

<template>

    <div class="filter">
        <div class="closeFilter" @click="showFilter.show = false">&times;</div>
        <div class="filter_param">
            <div class="label">Постановщик</div>
            <select :value="store.state.filter.creator_user_id || ''" @change="setFilter( 'creator_user_id' , $event )">
                <option value=""></option>
                <option v-for="user in users" :value="user.id" :key="user.id">
                    {{ user.name }}
                </option>
            </select>
        </div>

        <div class="filter_param">
            <div class="label">Дата создания</div>
            <input type="datetime-local" :value="store.state.filter.created_range || ''" @input="setFilter( 'created_range' , $event )">
        </div>

        <div class="filter_param">
            <label><div class="label">Ответственный</div>
                <select :value="store.state.filter.responsible_user_id || ''" @change="setFilter( 'responsible_user_id' , $event )">
                    <option value=""></option>
                    <option v-for="user in users" :value="user.id" :key="user.id">
                        {{ user.name }}
                    </option>
                </select>
            </label>
        </div>
        <div class="filter_param">
            <label><div class="label">Дата окончания</div>
                <input type="datetime-local" :value="store.state.filter.complete_range || ''" @input="setFilter( 'complete_range' , $event )">
            </label>
        </div>
        <div class="filter_param">
            <label><div class="label">Название</div>
                <input type="text" :value="store.state.filter.title || ''" @input="setFilter( 'title' , $event )">
            </label>
        </div>

        <button @click="showFilter.show = false; store.commit('setFilter', {'page': undefined}); getTasks()">
            Найти
        </button>
        <button @click="showFilter.show = false; store.commit('setFilter'); getTasks()">
            Сбросить
        </button>
    </div>

</template>

<script lang="ts">

    var users = ref([]);

    function setFilter(name, event){
        var field = {};
        field[name] = event.target.value;
        store.commit('setFilter', field )
    }

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
    
</script>

<style>

    .filter{
        position: absolute;
        background: #fff;
        padding: 20px 20px 10px;
        z-index: 10;
        box-shadow: 0px 0px 11px -4px  var(--shadow-color);
        min-width: 300px;
    }

    .filter input, .filter select {
        width: 100%;
    }

    .filter .closeFilter{
        position: absolute;
        top: 5px;
        right: 15px;
        cursor: pointer;
        font-size: 22px;
        line-height: 1;
    }

    .filter .closeFilter:hover{
        color: var(--button-color);
    }

    .filter .filter_param:not(:last-child){
        margin-bottom: 20px;
    }

    .filter .label{
        margin-bottom: 5px;
    }

</style>