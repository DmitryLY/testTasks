<script setup lang="ts">

    import { ref } from 'vue';
    import { useRoute, RouterLink } from 'vue-router';
    import Filter from './test_tasks_filter.vue';

    import store from "./../js/store";
    var route = useRoute();
    

    route.query.page && store.commit('setFilter', {page: route.query.page});
    getTasks();

</script>


<template v-if="tasks.length">

    <Filter v-if="showFilter.show" :getTasks="getTasks" :showFilter="showFilter" />
    <button class="button_filter" @click="showFilter.show = true">Фильтр</button>
    <div class="tasks">
        <table>
            <thead>
                <tr>
                    <td>Постановщик</td>
                    <td>Ответственный</td>
                    <td>Название</td>
                    <td>Дата создания</td>
                    <td>Дата окончания</td>
                    <td>Описание</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                <tr v-for="task in tasks" >
                    <td>{{ task.creator_user }}</td>
                    <td>{{ task.responsible_user }}</td>
                    <td>{{ task.title }}</td>
                    <td>{{ task.created_at }}</td>
                    <td>{{ task.complete_at }}</td>
                    <td>{{ task.description }}</td>
                    <td>{{ store.state.auth && task.canEdit && '&#128504;' }}</td>
                    <RouterLink  :to="{name: 'task', params: {'id': task.id}}"></RouterLink>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="paginator" v-if="paginator.last_page > 1">
        <a :class="{'active': page == paginator.current_page }" v-for=" page in paginator.last_page" :href="route.path + '?page=' +page" @click="store.commit('setFilter', {page}); getTasks($event)" >{{ page }}</a>
    </div>

</template>

<script lang="ts">

    var paginator = ref([]);
    var tasks = ref([]);
    var showFilter = ref({show: false});

    export default {
        components: [
            Filter
        ]
    }

    async function getTasks( e ){

        e instanceof Event && e.preventDefault();

        var sendData = '';
        var headers = {
                "X-CSRF-TOKEN": store.state.CSRF,
                "Authorization": `Bearer ${store.state.auth?.token}`
            }

        var filter = store.state.filter;

        if( Object.keys( filter ).length ){
            sendData = {};
            
            for( let field in filter ){
                if( !filter.hasOwnProperty( field ) || !filter[field] || ( typeof filter[field] === 'string' && !filter[field].trim() ) ) continue;
                sendData[field] = filter[field];
            }
            sendData = new URLSearchParams( sendData ).toString();
        }
        
        var json = await fetch('/public/api/tasks/' + ( sendData ? '?' + sendData : '' ), {
            method: 'GET',
            credentials: "same-origin",
            headers
        }).then(function(response) {
            return response.json();
        });

        tasks.value.splice(0) , ( tasks.value = tasks.value.concat( json.data ) );
        paginator.value = json.paginator;

    }

</script>

<style>

    .tasks tr{
        position: relative;
    }

    button.button_filter{
        background: #95a5af;
    }
    
    .tasks td{
        padding: 10px;
    }

    .tasks tbody td{
        border-top: 1px solid var(--shadow-color);
    }

    .tasks td:not(:first-child){
        border-left: 1px solid #f3f3f3;
    }

    .tasks tr > a{
        position: absolute;
        left: 0px;
        top: -1px;
        right: 0px;
        bottom: 0px;
        z-index: 1;
    }

    .tasks tr > a:hover{
        border: 1px solid var( --hover-color );
    }

    .paginator {
        margin: 10px 5px 0px;
    }

    .paginator a:hover, .paginator a.active{
        color: var( --hover-color );
    }

    .paginator a{
        padding: 0px 6px;
    }

    .paginator a:not(:last-child){
        margin-right: 4px;
    }

</style>