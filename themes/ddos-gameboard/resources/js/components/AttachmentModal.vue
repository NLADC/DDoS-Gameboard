<template>
    <transition name="modal" v-if="show">
        <div class="modal-mask">
            <div id="AttachmentModal" class="modal system-modal modal-autoscale">

                <div class="modal-header">
                    <div class="headings">
                        <h3 class="text-xl lg:text-4xl sm:text-2xl xm:text-1xl"><span
                            v-html="l('theme.attachment')"></span>:</h3>
                        <h6>{{ attachmentmodal.filename }}</h6>
                    </div>
                    <button type="reset" @click="close()" class="btn btn-secondary btn-small close-button">X</button>
                </div>

                <div v-if="attachmentmodal.isimage" class="attachment-preview preview-image">
                    <img id="attachmentModalPreviewImage" :src="this.attachmentmodal.base64string">
                </div>

                <div v-if="attachmentmodal.istext" class="attachment-preview preview-text">
                    <div>
                        <object :data="this.attachmentmodal.base64string" type="text/plain"
                                width="500" style="height: 300px">
                        </object>
                    </div>
                </div>

                <div v-if="attachmentmodal.ispdf" class="attachment-preview preview-pdf">
                    <embed :src="this.attachmentmodal.base64string" type="application/pdf" width="100%" height="1000px">
                </div>

                <div v-if="attachmentmodal.isvideo" class="attachment-preview preview-video">
                    <video controls width="320">
                        <source :src="this.attachmentmodal.base64string" type="video/mp4">
                        <source :src="this.attachmentmodal.base64string" type="video/ogg">
                        <source :src="this.attachmentmodal.base64string" type="video/webm">
                        Your browser does not support the video tag.
                    </video>
                </div>

                <div v-if="attachmentmodal.isaudio" class="attachment-preview preview-audio">
                    <audio
                        controls
                        :src="this.attachmentmodal.base64string">
                    </audio>
                </div>

                <div class="attachment-preview preview-download">
                    <h5 class="heading">
                      <span v-if="attachmentmodal.filename">
                        <br>
                        <a target="_blank" :download="attachmentmodal.filename"
                           :href="attachmentmodal.base64string">{{ attachmentmodal.filename }}</a>
                      </span>
                        <span v-else>{{ l('theme.cantpreview') }}</span>
                    </h5>
                    <a :download="this.attachmentmodal.filename" :href="this.attachmentmodal.base64string"
                       class="btn btn-secondary downloadbtn" v-html="l('theme.download')"></a>
                </div>
                <span class="created_at">created at: {{ attachmentmodal.created_at }}</span>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    props: {
        /*csrf: String,*/
        show: Boolean,
        attachmentmodal: Object,
    },

    data() {
        return {
            preview: false,
        }
    },


    methods: {
        close() {
            this.emitter.emit('emptyAttachmentsmodal');
            this.$emit('close')
        },
    }

}
</script>

<style scoped>
</style>
