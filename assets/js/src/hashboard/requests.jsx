/*!
 * wpdeps=hb-components-loading,wp-api-fetch,hashboard-rest,hb-components-pagination, hb-filters-moment
 */

/*global Vue:false*/

const $ = jQuery;

Vue.component( 'hametuha-request', {
  template: `
    <li class="hametuha-request-item list-group-item">
        <div class="d-flex w-100 justify-content-between mb-1">
          <h5>共同編集者としての招待</h5>
          <small>{{ request.updated | moment( 'lll' ) }}</small>
        </div>
        <p class="mb-1 text-muted">
           作品集『<a :href="request.permalink">{{request.post_title}}</a>』に {{request.name}}さんが（{{request.label}}）として招待されています。
           <span v-if="approved">報酬は <strong>{{ revenue }}%</strong> です。</span>
           <span v-else>まだこの招待を受け付けていません。</span>
        </p>
        <p class="text-muted text-right">
            <small class="badge badge-light">アクション</smallc>
        </p>
        <p class="text-right hametuha-request-actions">
            <button v-for="btn in actions" :class="btn.className" :key="btn.method" @click="handleClick( btn.method )">
                {{ btn.label }}
            </button>
        </p>
    </li>
  `,
  props: {
    type: {
      type: String,
      default: '',
    },
    request: {
      type: Object,
      default: {},
    },
  },
  computed: {
    approved: function() {
      return this.request.ratio >= 0;
    },
    revenue: function() {
      return parseInt( this.request.ratio, 10 );
    },
    actions: function() {
      const btn = [];
      if ( ! this.approved ) {
        btn.push({
          label: '承諾する',
          method: 'approve',
          className: 'btn btn-success btn-sm',
        });
      }
      btn.push( {
        label: '辞退する',
        method: 'delete',
        className: 'btn btn-danger btn-sm',
      } );
      return btn;
    }
  },
  mounted: function() {

  },
  methods: {
    handleClick: function( method ) {
      switch ( method ) {
        case 'approve':
          this.$emit( 'request-approved', this.request.id, this.request.post_id );
          break;
        case 'delete':
          if ( window.confirm( '辞退してもよろしいですか？　この操作は取り消せません。' ) ) {
            this.$emit( 'request-denied', this.request.id, this.request.post_id );
          }
          break;
        default:
          console && console.log( `Undefined method '${method}' is invoked.` );
          break;
      }
    }
  }
});

new Vue( {

  el: '#hametuha-requests',

  template: `
    <div class="hametuha-hb-request-list" data-loading-wrapper="true" style="position: relative;">
      <div v-if="total">
        <p class="text-muted text-right">{{curPage}} / {{total}}ページ</p>
      </div>
      <ul v-if="requests.length" class="notification-loop-container list-group">
        <hametuha-request class="notification-loop" v-for="request in requests" :request="request" :type="type" @request-approved="approvedHandler" @request-denied="deniedHandler"></hametuha-request>
      </ul>
      <div v-else-if="!loading" class="alert alert-secondary">
        <p>リクエストはありません。</p>
      </div>
      <hb-pagination v-if="total > 1" :total="total" :current="curPage" @pageChanged="pagination"></hb-pagination>
      <hb-loading :loading="loading" title="読み込み中……"></hb-loading>
    </div>`,

  data: {
    type: '',
    curPage: 1,
    total: 0,
    loading: false,
    requests: []
  },

  props: {
    type: {
      type: String,
      required: true
    }
  },

  beforeMount: function () {
    this.type = this.$el.getAttribute('data-type');
  },

  mounted: function () {
    this.fetch( 1 );
  },

  methods: {
    fetch: function( page ) {
      this.loading = true;
      wp.apiFetch( {
        path: `/hametuha/v1/collaborators/invitations/me?paged=${page}`,
        parse: false,
      }).then( res => {
        this.curPage = page;
        this.total   = res.headers.get( 'X-WP-Total-Pages' );
        return res.json();
      }).then( res => {
        this.requests = res;
      }).catch( res => {
        res.json().then( json => {
          $.hbErrorMessage( json.message );
        }).catch( res => {
          $.hbErrorMessage( 'エラーが発生しました。' );
        });
      }).finally( () => {
        this.loading = false;
      });
    },

    pagination: function( number ) {
      this.fetch( number );
    },

    approvedHandler( userId, postId ) {
      this.loading = true;
      wp.apiFetch( {
        path: 'hametuha/v1/collaborators/invitations/me',
        method: 'POST',
        data: {
          series_id: postId,
        }
      } ).then( res => {
        $.hbMessage( res.message, 'success' );
        this.fetch( this.curPage );
      }).catch( res => {
        let message = 'エラーが発生しました。';
        if ( res.message ) {
          message += res.message;
        }
        $.hbErrorMessage( message );
      }).finally( res => {
        this.loading = false;
      });
    },

    deniedHandler( userId, postId ) {
      this.loading = true;
      wp.apiFetch( {
        path: 'hametuha/v1/collaborators/invitations/me',
        method: 'DELETE',
        data: {
          series_id: postId,
        }
      } ).then( res => {
        $.hbMessage( res.message, 'success' );
        this.fetch( this.curPage );
      }).catch( res => {
        let message = 'エラーが発生しました。';
        if ( res.message ) {
          message += res.message;
        }
        $.hbErrorMessage( message );
      }).finally( res => {
        this.loading = false;
      });
    }

  }
});
