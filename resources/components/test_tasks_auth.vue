<script setup lang="ts">

    import { ref } from 'vue'
    import store from "./../js/store";
    init();
    
</script>

<template>
    <div class="auth">
        <div class="field">
            <label>
                <div class="label">Имя</div>
                <input type="text" name="name" v-model="name">
            </label>
        </div>
        <div class="field">
            <label>
                <div class="label">Email</div>
                <input type="text" name="email" v-model="email">
            </label>
        </div>
        <div class="field">
            <label>
                <div class="label">Пароль</div>
                <input type="password" name="password" v-model="password">
            </label>

        </div>

        <div v-if="errors?.length" >
            <div v-for="error in errors">
                {{ error }}
            </div>
        </div>

        <button @click="send(false)">
            Войти
        </button>
        <button @click="send(true)">
            Регистрация
        </button>
    </div>
</template>

<script lang="ts">

    var errors = ref([]);
    var name = '';
    var email = '';
    var password = '';


    function init(){
        errors.value.splice( 0 )
        name = '';
        email = '';
        password = '';
    }

    async function send(registration){
        errors.value.splice( 0 )

        var json;

        var sendData = new FormData;
        sendData.append('name', name);
        sendData.append('email', email);
        sendData.append('password', password);

        json = await fetch('/public/api/' + ( registration ? 'registration' : 'login' ), {
            method: 'POST',
            body: sendData,
            credentials: "same-origin",
            headers: {
                "X-CSRF-TOKEN": store.state.CSRF,
                "Authorization": `Bearer ${store.state.auth?.token}`
            }
        }).then(function(response) {
            return response.json();
        });

        if( json.auth ){
            store.commit('setAuth', json.auth );
            window.dispatchEvent( new CustomEvent( 'toMain' ) );
        }else if( json.errors ) {
            errors.value.splice( 0 ); ( errors.value = errors.value.concat( json.errors ) );
        }
        
    };

</script>

<style>

    .auth .field{
        margin-bottom: 15px;
    }

    .auth .field .label{
        margin-bottom: 5px;
    }

</style>