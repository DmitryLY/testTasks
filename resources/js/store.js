
import { createStore } from 'vuex'

export default createStore({
    state:{
        task: undefined,
        auth: undefined,
        CSRF: undefined,
        filter: {}
    },
    mutations: {
      setAuth( state, auth ){

        if( !auth ){
          auth = localStorage.getItem('Auth');
          auth && ( auth = JSON.parse( auth ) );
        }

        if( !auth?.token )
          return;

        state.auth = auth;
        localStorage.setItem('Auth', JSON.stringify( auth ) );
      },
      removeAuth(state){
        localStorage.removeItem('Auth');
        state.auth = undefined;
      },
      setCSRF(state, CSRF ){
        state.CSRF = CSRF;
      },
      setTask(state, task ){
        state.task = task;
      },
      setFilter( state, filter ){

        if( !filter ) 
          state.filter = {};
        else
          for( let field in filter ){
            if( !filter.hasOwnProperty(field) ) continue;
            
            state.filter[field] = filter[field];
          }
      }
    },
    getters: {
      getToken(state ){
        if( !state.token )
          return localStorage.getItem('token')

        return state.token;
      },
      getCSRF(state ){
        return state.CSRF;
      },
      getTask(state ){
        return state.task;
      }
    }
  })
  
