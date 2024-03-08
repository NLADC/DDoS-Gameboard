<!--
  - Copyright (C) 2024 Anti-DDoS Coalitie Netherlands (ADC-NL)
  -
  - This file is part of the DDoS gameboard.
  -
  - DDoS gameboard is free software; you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation; either version 3 of the License, or
  - (at your option) any later version.
  -
  - DDoS gameboard is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with this program; If not, see @link https://www.gnu.org/licenses/.
  -
  -->

<template>
    <transition name="modal" v-if="show">
        <div class="modal-mask">
            <div class="modal system-modal modal-large">

              <div class="modal-header">
                <h3 class="text-xl lg:text-4xl sm:text-2xl xm:text-1xl" v-html="l('theme.help')"> </h3>
                <button type="reset" @click="close()" class="btn btn-secondary btn-small close-button">X</button>
              </div>


              <div class="help-file">
                <p v-html="l('help.helpcontent')"> </p>

                    <div class="flex bg-grey-lighter">
                        <!--<div class="flex-none text-center px-4 py-2 m-2"><b class="text-xl">02</b></div>-->
                        <div class="flex-none text-center px-4 py-2 m-2"><i class="material-icons">filter_2</i></div>
                        <div class="flex-auto text-left px-4 py-2 m-2"v-html="l('help.timeline')"></div>
                    </div>
                    <div class="flex bg-grey-lighter">
                        <div class="flex-none text-center px-4 py-2 m-2"><i class="material-icons">alternate_email</i></div>
                        <div class="flex-auto text-left px-4 py-2 m-2"v-html="l('help.gamecolour')"></div>
                    </div>
                    <div class="flex bg-grey-lighter">
                        <div class="flex-none text-center px-4 py-2 m-2"><i class="material-icons">list</i></div>
                        <div class="flex-auto text-left px-4 py-2 m-2"v-html="l('help.livelogs')"></div>
                    </div>
                    <div class="flex bg-grey-lighter">
                        <div class="flex-none text-center px-4 py-2 m-2"><i class="material-icons mi-business">business</i></div>
                        <div class="flex-auto text-left px-4 py-2 m-2"v-html="l('help.parties')"></div>
                    </div>
                    <div class="flex bg-grey-lighter">
                        <div class="flex-none text-center px-4 py-2 m-2"><i class="material-icons">event</i></div>
                        <div class="flex-auto text-left px-4 py-2 m-2"v-html="l('help.actionssummary')"></div>
                    </div>
                    <div class="flex bg-grey-lighter">
                        <div class="flex-none text-center px-4 py-2 m-2"><i class="material-icons">insert_comment</i></div>
                        <div class="flex-auto text-left px-4 py-2 m-2"v-html="l('help.quickreply')"></div>
                    </div>
                    <div class="flex bg-grey-lighter">
                        <div class="flex-none text-center px-4 py-2 m-2"><i class="material-icons mi-help-outline">help_outline</i></div>
                        <div class="flex-auto text-left px-4 py-2 m-2"v-html="l('help.readme')"></div>
                    </div>
                    <div class="flex bg-grey-lighter">
                        <div class="flex-none text-center px-4 py-2 m-2"><i class="material-icons">play_circle_outline</i></div>
                        <div class="flex-auto text-left px-4 py-2 m-2"v-html="l('help.countdown')"></div>
                    </div>
                    <div class="flex bg-grey-lighter">
                        <div class="flex-none text-center px-4 py-2 m-2"><i class="material-icons" v-html="l('theme.logout')"></i></div>
                        <div class="flex-auto text-left px-4 py-2 m-2"v-html="l('help.accountname')"></div>
                    </div>
                    <div class="flex bg-grey-lighter">
                        <div class="flex-none text-center px-4 py-2 m-2"><i class="material-icons">priority_high</i></div>
                        <div class="flex-auto text-left px-4 py-2 m-2"v-html="l('help.actionhasissue')"></div>
                    </div>
                    <div class="flex bg-grey-lighter">
                        <div class="flex-none text-center px-4 py-2 m-2"><i class="material-icons">clear</i></div>
                        <div class="flex-auto text-left px-4 py-2 m-2"v-html="l('help.actioniscancelled')"></div>
                    </div>
                    <div class="flex bg-grey-lighter">
                        <div class="flex-none text-center px-4 py-2 m-2"><i class="material-icons action-wrapper-bg">&nbsp;&nbsp;&nbsp;</i></div>
                        <div class="flex-auto text-left px-4 py-2 m-2"v-html="l('help.actionscheduled')"></div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <button type="reset" @click="close()" class="w-full py-2 px-4 rounded-r focus:outline-none focus:shadow-outline"v-html="l('theme.close')"></button>
                </div>

            </div>
        </div>
    </transition>
</template>

<script>
    export default {
        props: {
            show: Boolean
        },

        methods: {
            close() {
                this.$emit('close')
            }
        }

    }
</script>

<style scoped>
</style>
