import { createRouter, createWebHistory } from 'vue-router'
import auth from '/resources/components/test_tasks_auth.vue'
import tasks from '/resources/components/test_tasks_tasks.vue'
import task from '/resources/components/test_tasks_task.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'index',
      component: tasks,
      props: route => ({ query: route.query.page || '' }),
      meta: {
        title: 'Задачи'
      }
    },
    {
      path: '/auth',
      name: 'auth',
      component: auth,
      meta: {
        title: 'Авторизация'
      }
    },
    {
        path: '/task',
        component: task,
        meta: {
          title: 'Создать задачу'
        },
        children: [
          {
            path: ':id',
            name: 'task',
            component: task,
            meta: {
              title: 'Страница задачи'
            }
          },

        ]
      },
      
  ],
})

export default router
