<template>
    <div class="j-video-fetcher">
        <div class="video-url video-input">
            <input 
                type="text" 
                :class="{'on-fetcher': videoType === 'fetch'}"
                :placeholder="placeholder"
                v-model="value"
                @input="onInput"
                @focus="onFocus" >
        </div>
        <JUrlPreview :data="preview" @removePreview="removePreview"/>
    </div>
</template>

<script>

import JUrlPreview from '../_components/JUrlPreview.vue';
import JDropdownSelect from '../_components/JDropdownSelect.vue';
//import sortCategories from '../../../utils/sort-categories';
import {constants} from '../../../utils/constants';
import debounce from 'lodash/debounce';
import language from '../../../utils/language';
//import JFileComposer from './JFileComposer.vue';

export default {
    components: {
        //JVideoComposer,
        JUrlPreview,
        JDropdownSelect,
        //JFileComposer,
    },

    props: {
        videoType: {
            type: String,
            default: '',
        }
    },

    data() {
        //const sorted = sortCategories(constants.get('videoCategories'));

        return {
            preview: {
                id: '',
                url: '',
                title: '',
                desc: '',
                image: '',
            },
            urlAppeared: false,
            value: '',
            placeholder: language('video.share_button')
        }
    },

    computed: {
        hasVideo() {
            return !!this.$store.state.video.attachment.fetch.length;
        },

        categoryId: {
            get() {
                return this.$store.state.video.catid;
            },

            set(value) {
                this.$store.commit('video/setCategory', value);
            },
        }
    },

    methods: {
        validate() {

          if (!this.preview.url) {
            return false;
          }
            this.post();
          return true;
        },

        post() {
            const DATA = Joomla.getOptions('com_community');
            const filterParams = DATA.stream_filter_params ? JSON.stringify(DATA.stream_filter_params) : '';

            const state = this.$store.state.video;
            const content = state.content;
            const attachments = JSON.stringify(state.attachment);
            const rawData = [content, attachments, filterParams];

            this.$store.dispatch('post', rawData).then(() => {
                this.$emit('reset');
            });
        },

        reset() {
            this.value = '';
            this.urlAppeared = false;
            this.resetPreview();

            this.$refs.composer && this.$refs.composer.reset();
        },

        onInput() {
            if (!this.urlAppeared) {
                this.fetchUrlPreview();
            }
        },

        onFocus() {
            this.$store.commit('setFree', false);
        },

        fetchUrlPreview: debounce(function() {
            const url = this.value;
            const urlRegex = /^(http|https):\/\/[a-z0-9-]+\.[a-z0-9-]+\S+$/;
            const match = url.match(urlRegex);

            if (!match) {
                return;
            }

            this.urlAppeared = true;
            this.$store.commit('setLoading', true);
            

            joms.ajax({
                func: 'videos,ajaxLinkVideoPreview',
                data: [ url ],
                callback: res => {
                    if (res[1][1] === '__throwError') {
                        alert(res[1][3]);
                    } else {
                        const data = res[1][3][0].video;
                        
                        this.preview.id = data.id;
                        this.preview.image = data.thumb;
                        this.preview.url = data.path;
                        this.preview.title = data.title;
                        this.preview.desc = data.description || '';
                        this.preview.catid = data.category_id;

                        this.$store.commit('video/setCategory', data.category_id);
                        this.$store.commit('video/setPreview', this.preview);
                        this.$emit('typeChange', 'fetch');
                    }

                    this.$store.commit('setLoading', false);
                }
            });
        }, 300),

        removePreview() {
            this.resetPreview();
            this.$store.commit('video/setPreview', false);
            this.$emit('reset');
        },

        resetPreview() {
            this.preview.id = '';
            this.preview.url = '';
            this.preview.title = '';
            this.preview.desc = '';
            this.preview.image = '';
        },
    },
}
</script>

<style lang="scss">
.j-video-fetcher {
    .video-category {
        padding: 8px;
        border-bottom: solid 1px #f5f5f5;
    }

    .joms-postbox-fetched {
        .joms-postbox-inner-panel {
            border: none;
        }
    }

    
    .video-input {
        padding: 10px 13px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: none;

        input {
          text-align: center !important;
          font-size: 16px;
          padding: 10px;
          border: 1px solid #d3d3d3;
          height: 100%;
          border-radius: 15px;
          margin: 0;

            &.on-fetcher {
                text-align: unset;
            }

            &:focus {
                box-shadow: none;
            }
        }
    }

    .ql-editor.ql-blank::before {
        font-style: unset;
    }
}
</style>