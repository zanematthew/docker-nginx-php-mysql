<template>
  <div>
  <!-- Share -->
  <router-link :to="{ name: 'share' }" class="grid is-100 row is-item">
    <icon name="share-square-o" class="align-icon"></icon> Share
  </router-link>

  <!--
  |
  | Name: Action Edit
  | Type: Schedule
  | Action Items: Rename, View Events, Delete
  |
  -->
  <div v-if="type === 'schedule'">
    <router-link :to="{
      name: 'action-edit'
    }" class="grid is-100 row is-item" exact>
      <icon name="pencil-square-o" class="align-icon"></icon>Rename Schedule
    </router-link>
    <router-link :to="{
      name: 'schedule-single-page',
      params: {
        id: item.id,
        slug: item.slug
      }
    }" class="grid is-100 row is-item" exact>
      <icon name="calendar" class="align-icon"></icon>View Events
    </router-link>
    <schedule-button-delete :id="item.id" class="grid is-100 row is-item"></schedule-button-delete>
  </div>

    <!-- View Event -->
    <!-- Pass down the slug, and event.venue.id from the parent, via "meta" -->
    <router-link v-if="type === 'event'" :to="{ name: 'event-single-page',
    params: { id: item.id, slug: item.slug, when: 'this-month' },
    query: { venue_id: item.venue_id }
    }" class="grid is-100 row is-item">
      <icon name="calendar" class="align-icon"></icon>View Event...
    </router-link>

    <!-- View Venue -->
    <router-link :to="{
    name: 'venue-single-events',
    params: {
      venue_id: item.venue_id,
      slug: item.slug,
      when: 'this-month'
      }
    }" class="grid is-100 row is-item" v-if="type === 'venue' || type === 'event'">
      <icon name="map-o" class="align-icon"></icon>View Venue...
    </router-link>

    <!-- @todo Add/Remove -->
    <!-- Event -->
    <router-link v-if="type === 'event'" :to="{
      name: 'add-to',
      params: { id: item.id },
      query: { name: nameOrTitle }
    }" class="grid is-100 row is-item">
      <icon name="list-alt" class="align-icon"></icon>Add to Schedule
    </router-link>

  </div>
</template>
<script>
import { mapGetters } from 'vuex';
import scheduleButtonDelete from '~/components/ScheduleButtonDelete';

export default {
  components: {
    scheduleButtonDelete,
  },
  data() {
    return {
      item: {
        slug: 'default', // Prevents VueJS warning since router.js does not want slug to be ''
        id: 123,
        venue_id: 321
      }
    }
  },
  computed: {
    ...mapGetters([
      'type'
    ]),
    nameOrTitle() {
      return this.item.name || this.item.title;
    }
  },
  mounted() {
    // Build item?
    console.log(this.type);
    let venue_id = this.type === 'venue' ? this.$route.params.id : this.$route.query.venue_id;

    this.item = {
      id: this.$route.params.id,
      slug: this.$route.query.slug,
      name: this.$route.query.name,
      venue_id: venue_id
    };
  },
  metaInfo() {
    return {
      titleTemplate: 'Action | My BMX Events'
    }
  },
}
</script>
