/*
 * Copyright (c) 2020. The Nextcloud Bookmarks contributors.
 *
 * This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */
import Vue from 'vue'
import { Store } from 'vuex'
import { Tooltip } from '@nextcloud/vue'
import App from './App.vue'
import router from './router.js'
import store from './store/index.js'
import AppGlobal from './mixins/AppGlobal.js'
import DropTarget from './directives/drop-target.js'
import { subscribe } from '@nextcloud/event-bus'
import { generateUrl } from '@nextcloud/router'

Vue.mixin(AppGlobal)
Vue.directive('tooltip', Tooltip)
Vue.directive('drop-target', DropTarget)

const BookmarksApp = (global.Bookmarks = new Vue({
	el: '#content',
	store: new Store(store),
	router,
	created() {
		subscribe('nextcloud:unified-search.search', ({ query }) => {
			this.$router.push({ name: this.routes.SEARCH, params: { search: query } })
		})
		subscribe('nextcloud:unified-search.reset', () => {
			this.$router.push({ name: this.routes.HOME })
		})
	},
	render: h => h(App),
}))

if ('serviceWorker' in navigator) {
	navigator.serviceWorker.register(generateUrl('/apps/bookmarks/bookmarks-service-worker.js', {}, {
		noRewrite: true,
	}), {
		scope: generateUrl('/apps/bookmarks'),
	})
		.then(() => {
			console.info('ServiceWorker registered')
		})
		.catch(er => console.error(er))

	window.caches.open('js').then(async cache => {
		const url = generateUrl('/apps/bookmarks/js/bookmarks-main.js')
		cache.put(url, await fetch(url))
	})
} else {
	console.warn('ServiceWorker not supported')
}

export default BookmarksApp
