<script setup lang="ts">

    import { RouterLink, RouterView } from 'vue-router'
    import { useRoute, useRouter } from 'vue-router'
    import store from "./../js/store";

    const route = useRoute();
    const router = useRouter();

    router.beforeEach((to, from, next) => {
        const title = to.meta.title
        if (title) {
            document.title = title
        }
        next()
    });

    window.addEventListener('toMain', function(){
        router.replace('/');
    });

</script>

<template>

    <nav class="nav_app">
        <RouterLink to="/">Задачи</RouterLink>
        <RouterLink v-if="store.state.auth?.token && route.path !== '/task'" to="/task" :key="route.fullPath">Добавить задачу</RouterLink>
        <RouterLink class="auth" v-if="!store.state.auth?.token" to="/auth">Авторизация</RouterLink>
        
        <div v-if="store.state.auth?.token" class="auth">
            <span class="login_name">{{ store.state.auth.name }}</span>
            <button @click="logout">Выйти</button>
        </div>
    </nav>
    
    <div class="body_app">
        <RouterView />
    </div>

</template>


<script lang="ts">

    
    store.commit( 'setCSRF', document.querySelector('meta[name="csrf-token"]').getAttribute('content') );
    store.commit( 'setAuth' );

    async function logout(){
        var json = await fetch('/public/api/logout', {
            method: 'POST',
            credentials: "same-origin",
            headers: {
                "X-CSRF-TOKEN": store.state.CSRF,
                "Authorization": `Bearer ${store.state.auth?.token}`
            }
        }).then(function(response) {
            return response.json();
        });

        if( json['success'] ){
            store.commit( 'removeAuth', undefined, true );
        }

    }

</script>

<style>

    #app{
        padding: 20px;
        max-width: 1300px;
        box-shadow: 0px 0px 10px -3px var(--shadow-color);
        margin: 20px auto;    
        --button-color: #5a8daf;
        --shadow-color: #d1d1d1;
        --hover-color: #5a8daf;
    }

    nav .auth{
        float: right;
    }

    nav .auth .login_name{
        margin-right: 20px;
    }

    nav.nav_app > *{
        display: inline-block;
        margin: 0px 2px;
    }

    .body_app{
        margin: 20px 0 10px;
        position: relative;
    }

    nav.nav_app > a, nav.nav_app .auth button, .body_app button{
        padding: 5px 9px;
        margin: 5px 10px 5px 0px;
        background: var(--button-color);
        color: #fff;
        text-transform: uppercase;
        font-size: 13px;
    }

    nav.nav_app > a:hover, nav.nav_app .auth button:hover, .body_app button:hover{
        box-shadow: 0px 0px 11px 0px  var(--shadow-color);
    }

    
    .tasks > table{
        width: 100%;
    }

</style>