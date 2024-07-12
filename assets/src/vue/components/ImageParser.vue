<template>
  <div>
    <h1>Image Parser</h1>
    <form @submit.prevent="fetchImages">
      <input type="text" v-model="url" placeholder="Enter URL">
      <button type="submit">Fetch Images</button>
    </form>
    <div id="imageInfo">
      <p v-if="hold">Loading...</p>
      <p v-if="images.length">Found {{ images.length }} images</p>
      <p v-if="totalSize">Total size {{ (totalSize / 1024 / 1024).toFixed(2) }} Mb</p>
    </div>
    <div id="imageContainer" class="image-grid">
      <div v-for="(src, index) in images" :key="index" class="image-wrapper">
        <img :src="src" :alt="'Image ' + index">
      </div>
    </div>
  </div>
</template>

<script>
import { watch } from "vue";

export default {
  name: 'ImageParser',
  data() {
    return {
      url: '',
      hold: false,
      responseMessage: '',
      images: [],
      totalSize: 0
    };
  },
  methods: {
    async fetchImages() {
      this.hold = true;
      const response = await fetch('/fetch', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({url: this.url})
      });
      this.responseMessage = await response.json();

      this.images = this.responseMessage.images;
      this.totalSize = this.responseMessage.totalSize;
      this.hold = false;
    }
  },
  mounted() {
    watch(
        () => this.responseMessage,
        (newMessage) => {
          if (newMessage) {
            this.hold = false;
          }
        }
    );
  }
}
</script>

<style scoped>
.image-grid {
  display: flex;
  flex-wrap: wrap;
}

.image-wrapper {
  flex: 1 1 21%;
  margin: 10px;
  box-sizing: border-box;
}

.image-wrapper img {
  max-width: 100%;
  height: auto;
  display: block;
}
</style>
