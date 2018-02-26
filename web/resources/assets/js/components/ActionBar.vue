<template>
  <div v-if="item.id" class="row is-item">

    <!-- Library Area -->
    <library-item-toggle :item_id="item.id" :item_type="type"></library-item-toggle>

    <!-- Image Area -->
    <div class="avatar-container" v-if="imageUri">
      <div class="avatar">
        <img :src="imageUri" itemprop="image" alt="Photo of Jane Joe">
      </div>
    </div>

    <!-- Title Area (Event) -->
    <router-link v-if="type === 'event'"
      :to="{
      name: 'event-single-page',
      params: { id: item.id, slug: item.slug, when: 'this-month' },
      query: { venue_id: item.venue.id }
      }"
      class="text-area" exact>
        <div class="title">{{ item.title }}</div>
        <div class="not-title" v-if="item.start_date">
          {{ startDate(item.start_date) }}
          <router-link :to="{
          name: 'event-list-page',
          query: {
            state: stateAbbr,
            this_month: true
          },
          params: {
            when: 'this-month'
          }
        }"
        v-if="item.venue"
        class="not-active"> &bull; {{ item.venue.city.name }}<span v-if="stateAbbr">, {{ stateAbbr }}</span>
        </router-link>
        </div>
    </router-link>

    <!-- Title Area (Venue) -->
    <router-link v-if="type === 'venue'"
      :to="{
      name: 'venue-single-events',
      params: { venue_id: item.id, slug: item.slug, when: 'this-month' }
      }" class="text-area" exact>
      <div class="title">{{ item.name }}</div>
      <div
        class="not-title"
        v-if="item.city">
        {{ item.city.name }}<span v-if="stateAbbr">, {{ stateAbbr }}</span>
      </div>
    </router-link>

    <!-- Title Area (Schedule) -->
    <router-link v-if="type === 'schedule'" :to="{ name: 'schedule-single-page',
      params: { id: item.id, slug: item.slug }
      }" class="text-area" exact>
      <div class="title">{{ item.name }}</div>
      <div class="not-title">Updated {{ fromNow(item.updated_at) }}</div>
    </router-link>

    <!-- Detail Area -->
    <router-link :to="{
      name: 'action-main',
      params: { id: item.id, type: type },
      query: { slug: item.slug, name: nameOrTitle, venue_id: item.venue_id }
    }" class="ellipsis-container"><icon name="ellipsis-h"></icon></router-link>
  </div>
  <div v-else class="align-center row is-item grid is-100">
    <icon name="refresh" spin></icon>
  </div>

</template>
<script>
import MyMixin from '~/mixin.js';
import libraryItemToggle from '~/components/LibraryItemToggle';

export default {
  mixins: [MyMixin],
  props: {
    item: { id: '' },
    type: {
      type: String,
      required: true
    }
  },
  computed: {
    imageUri() {
      if (this.type === 'schedule')
        return;
      return this.item.image_uri || this.item.venue.image_uri;
    },
    nameOrTitle() {
      return this.item.name || this.item.title;
    },
    // @todo I really need to fix this on the model level :\
    stateAbbr() {
      if (this.type === 'venue') {
        if (_.isEmpty(this.item.city)) {
          return 'Missing City';
        }
        if (_.isEmpty(this.item.city.states)) {
          return 'Missing State';
        }
        if (this.item.city.states.length > 0) {
          return this.item.city.states[0].abbr;
        }
      } else if (this.type === 'event') {
        if (this.item.venue.city.states.length > 0) {
          return this.item.venue.city.states[0].abbr;
        }
      } else {
        return 'MD';
      }
    },
    venueId() {
      if (this.type === 'venue') {
        return this.item.id;
      } else {
        return this.item.venue.id;
      }
    }
  },
  components: {
    libraryItemToggle
  }
}
</script>
<style lang="scss">
@import "../../sass/variables";
.not-active {
  color: $link-gray;
}
.avatar-container {
  float: left;
  width: 15%;
  text-align: center;
}
.ellipsis-container {
  width: 15%;
  float: right;
  text-align: center;
}
.text-area {
  width: 60%;
  float: left;
  padding-right: 10px;
  padding-left: 5px;
}
</style>