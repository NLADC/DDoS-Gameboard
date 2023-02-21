<template>
  <div class="log-bubble" @click="editLog" :title="{'edit log': log.showedit}"
       :class="{'log-bubble-edit': log.showedit,
                'redparty': this.isRedParty(),
                'blueparty': this.isBlueParty(),
       }">
    <div class="flex flex-row w-full">
      <div :class="{'w-11/12': log.showedit, 'w-full': !log.showedit}">
        <div class="message">{{ log.log }}</div>
        <div class="attachments">
          <li v-for="attachment in attachments" :key="log.attachments.id">
            <a class="attachmentlink"
               @click="openAttachmenModel((attachment.id), $event.target)">{{ attachment.file_name }}
              <div class="loading_animation"></div>
            </a>

          </li>
        </div>
        <div class="flex">
          <div class="timestamp">{{ this.moment(log.timestamp).format('HH:mm') }}</div>
          <div class="author"
               :class="{'purplecolor': this.isPurpleParty(),
                'redcolor': this.isRedParty(),
                'bluecolor': this.isBlueParty(),
       }"
          >
            {{ log.user.party.name }} - {{ log.user.name }}
          </div>
        </div>
      </div>
      <div v-if=" log.showedit
          " class="editlogbutton w-1/12 text-right">
          <span class="material-icons text-sm">edit</span>
        </div>
      </div>
    </div>
</template>

<script>
export default {
  props: {
    log: Object,
    showedit: Boolean
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

    isRedParty() {
      if (this.log.user !== undefined) {
        return this.log.user.role === "red";
      }
    },

    isBlueParty() {
      if (this.log.user !== undefined) {
        return this.log.user.role === "blue";
      }
    },

    isPurpleParty() {
      if (this.log.user !== undefined) {
        return this.log.user.role === "purple";
      }
    },


    hideError() {
      this.isError = false;
      this.errorMsg = '';
    },

    editLog: function () {
      if (this.log.showedit) {
        this.$emit('edit-log', this.log);
      }
    },
    toggleAttachmentlinks(remove = false) {
      var attachmentlinks = document.querySelectorAll('a.attachmentlink');
      for (let i = 0; i < Object.keys(attachmentlinks).length; i++) {
        if (remove) {
          attachmentlinks[i].classList.remove('disabled');
        } else {
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
  }


};
</script>0
