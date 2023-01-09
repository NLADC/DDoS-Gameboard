<template>
  <div class="log-bubble quicklog-bubble">
    <p>{{ log.debug }}</p>
    <div class="attachments-wrapper">
      <div class="message">{{ log.log }}</div>
      <div class="attachments">
        <li v-for="attachment in attachments" :key="log.attachments.id">
          <a class="attachmentlink" @click="openAttachmenModel((attachment.id), $event.target)">{{ attachment.file_name }}
            <div class="loading_animation"></div>
          </a>

        </li>
      </div>
    </div>
    <div class="flex">
      <div class="timestamp">{{ this.moment(log.timestamp).format('HH:mm') }}</div>
      <div class="author">
        {{ log.user.party.name }} - {{ log.user.name }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    log: Object,
  },

  data() {
    return {
      isError: false,
      errorMsg: '',
      attachments: this.log.attachments,
      showloadingicon: true
    }
  },

  methods: {
    triggerError(msg) {
      this.isError = true;
      this.errorMsg = msg;
      alert(this.errorMsg)
      setTimeout(this.scrollBottom, 1000);
    },

    hideError() {
      this.isError = false;
      this.errorMsg = '';
    },

    toggleAttachmentlinks(remove = false) {
      var attachmentlinks = document.querySelectorAll('.quicklog-bubble .attachments a');
      for (let i = 0; i < Object.keys(attachmentlinks).length; i++) {
        if (remove) {
          attachmentlinks[i].classList.remove('disabled');
        }
        else {
          attachmentlinks[i].classList.add('disabled');
        }
      }
    },

    async openAttachmenModel(id, target) {
      this.hideError();
      var path = '/api/attachments';
      var method = 'POST';
      var tmp = {
        id: id,
      }
      let parentfound = false;

      if (target) {
        target.classList.add('loading');
        parentfound = true;
        this.toggleAttachmentlinks();
      }

      const response = await fetch(path, {
        method: method,
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(tmp)
      })
          .then(response => response.json())
          .then(data => {
            if ('result' in data && data.result === false && 'message' in data)
              this.triggerError(data.message);
            else {
              if ('result' in data && data.result === true) {
                Event.$emit('initAttachmentlog', data.file);
              }
            }
            if (parentfound) {
              target.classList.remove('loading');
            }
            this.toggleAttachmentlinks(true);
          })
          .catch(err => {
            this.triggerError('A fatal error has occured! ' + err);
          });

    }
  },
};
</script>
