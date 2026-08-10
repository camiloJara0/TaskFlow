<script setup lang="ts">
import LabelForm from './forms/LabelForm.vue'
import CommentForm from './forms/CommentForm.vue'
import SubtaskForm from './forms/SubtaskForm.vue'
import UploadFileForm from './forms/UploadFileForm.vue'
import ReactionPicker from './forms/ReactionPicker.vue'
import ReminderForm from './forms/ReminderForm.vue'
import MemberPicker from './forms/MemberPicker.vue'

const props = defineProps<{
  task: Record<string, any>
}>()
console.log(props.task)
const emit = defineEmits<{
  close: []
  saved: [task: Record<string, any>]
}>()

const { getById, getComments, getSubtasks, getFiles, getActivity } = useTasksService()

const task = ref<Record<string, any>>(props.task)
const activeTab = ref('details')
const addLabel = ref(false)
const addSubtask = ref(false)
const comments = ref<any[]>([])
const subtasks = ref<any[]>([])
const files = ref<any[]>([])
const activity = ref<any[]>([])
const deleting = ref(false)
const saving = ref(false)
const editCommentId = ref<number | null>(null)
const editCommentText = ref('')

async function refresh() {
  const res = await getById(task.value.id)
  task.value = { ...props.task, ...res.data }
}

async function fetchComments() {
  const res = await getComments(task.value.id)
  comments.value = res.data
}

async function fetchSubtasks() {
  const res = await getSubtasks(task.value.id)
  subtasks.value = res.data
}

async function fetchFiles() {
  const res = await getFiles(task.value.id)
  files.value = res.data
}

async function fetchActivity() {
  const res = await getActivity(task.value.id)
  activity.value = res.data
}

const statusItems = [
  { label: 'Pendiente', value: 'backlog' },
  { label: 'Por hacer', value: 'todo' },
  { label: 'En progreso', value: 'in_progress' },
  { label: 'Revisión', value: 'review' },
  { label: 'Completado', value: 'done' }
]

const priorityItems = ['Urgente', 'Alta', 'Media', 'Baja']

async function saveChanges() {
  saving.value = true
  try {
    const tasks = useTasksService()
    await tasks.update(task.value.id, {
      titulo: task.value.titulo,
      descripcion: task.value.descripcion || undefined,
      prioridad: task.value.prioridad,
      estado: task.value.estado,
      fecha_vencimiento: task.value.fecha_vencimiento || undefined,
      fecha_inicio: task.value.fecha_inicio || undefined,
      estimacion_horas: task.value.estimacion_horas || undefined
    })
    await refresh()
    emit('saved', task.value)
  } finally {
    saving.value = false
  }
}

async function deleteTask() {
  deleting.value = true
  try {
    const tasks = useTasksService()
    await tasks.remove(task.value.id)
    emit('saved', task.value)
    emit('close')
  } finally {
    deleting.value = false
  }
}

async function toggleSubtask(sub: any) {
  const tasks = useTasksService()
  await tasks.toggleSubtask(sub.id, !sub.estado)
  sub.estado = !sub.estado
}

async function removeSubtask(sub: any) {
  const tasks = useTasksService()
  await tasks.removeSubtask(sub.id)
  subtasks.value = subtasks.value.filter(s => s.id !== sub.id)
}

async function onCommentSaved() {
  await fetchComments()
}

async function removeComment(c: any) {
  const tasks = useTasksService()
  await tasks.removeComment(c.id)
  await fetchComments()
}

function startEditComment(c: any) {
  editCommentId.value = c.id
  editCommentText.value = c.comentario
}

async function saveEditComment() {
  if (editCommentId.value === null) return
  const tasks = useTasksService()
  await tasks.updateComment(editCommentId.value, editCommentText.value)
  editCommentId.value = null
  editCommentText.value = ''
  await fetchComments()
}

async function onReactionChanged() {
  await fetchComments()
}

async function onReminderSaved() {
  await refresh()
}

function avatarProps(c: any) {
  const user = c.usuario || {}
  if (user.foto) return { src: user.foto }
  const name = user.nombre || '?'
  const parts = name.trim().split(/\s+/)
  const initials = parts.length > 1
    ? (parts[0]?.[0] ?? '') + (parts[1]?.[0] ?? '')
    : name.slice(0, 2)
  return { text: initials.toUpperCase() }
}

function formatDateTime(value: string | undefined): string {
  if (!value) return ''
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value
  return d.toLocaleString('es-ES', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}

onMounted(async () => {
  await refresh()
  await Promise.all([fetchComments(), fetchSubtasks(), fetchFiles()])
  await fetchActivity()
})

const tabs = [
  { label: 'Detalles', id: 'details', icon: 'i-lucide-info' },
  { label: 'Subtareas', id: 'subtasks', icon: 'i-lucide-list-checks' },
  { label: 'Comentarios', id: 'comments', icon: 'i-lucide-message-square' },
  { label: 'Archivos', id: 'files', icon: 'i-lucide-paperclip' },
  { label: 'Historial', id: 'history', icon: 'i-lucide-history' }
]
</script>

<template>
  <div class="flex flex-col h-full">
    <div class="relative flex items-center justify-between px-5 h-16 border-b border-default shrink-0">
      <div class="absolute top-0 left-0 right-0 h-0.5 bg-linear-to-r from-blue-500 via-cyan-400 to-blue-500" />
      <span class="text-sm font-heading font-semibold truncate">{{ task.titulo || 'Detalle de tarea' }}</span>
      <UButton
        icon="i-lucide-x"
        size="sm"
        color="neutral"
        variant="ghost"
        class="rounded-lg"
        @click="emit('close')"
      />
    </div>

    <div class="flex items-center gap-1 px-4 border-b border-default shrink-0 overflow-x-auto scrollbar-thin py-1.5">
      <UButton
        v-for="tab in tabs"
        :key="tab.id"
        :icon="tab.icon"
        :label="tab.label"
        size="xs"
        color="neutral"
        :variant="activeTab === tab.id ? 'soft' : 'ghost'"
        class="rounded-lg whitespace-nowrap"
        :class="activeTab === tab.id
          ? 'bg-blue-500/10! text-blue-600! dark:text-blue-400! ring-1 ring-blue-500/20'
          : ''"
        @click="activeTab = tab.id"
      />
    </div>

    <div class="flex-1 overflow-y-auto p-4 space-y-4">
      <div v-if="activeTab === 'details'">
        <div class="space-y-3">
          <div>
            <label class="text-xs font-medium text-muted mb-1 block">Título</label>
            <UInput
              v-model="task.titulo"
              size="sm"
              class="w-full"
            />
          </div>
          <div>
            <label class="text-xs font-medium text-muted mb-1 block">Descripción</label>
            <UTextarea
              v-model="task.descripcion"
              size="sm"
              class="w-full"
              :rows="4"
            />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-xs font-medium text-muted mb-1 block">Estado</label>
              <USelect
                v-model="task.estado"
                size="sm"
                class="w-full"
                :items="statusItems"
              />
            </div>
            <div>
              <label class="text-xs font-medium text-muted mb-1 block">Prioridad</label>
              <USelect
                v-model="task.prioridad"
                size="sm"
                class="w-full"
                :items="priorityItems"
              />
            </div>
          </div>
          <div>
            <label class="text-xs font-medium text-muted mb-1 block">Asignado a</label>
            <MemberPicker
              v-model="task.responsable_id"
              :multiple="false"
            />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-xs font-medium text-muted mb-1 block">Fecha límite</label>
              <UInput
                :model-value="task.fecha_vencimiento?.split('T')[0]"
                @update:model-value="value => task.fecha_vencimiento = value"
                type="date"
                size="sm"
                class="w-full"
              />
            </div>
            <div>
              <label class="text-xs font-medium text-muted mb-1 block">Estimación (horas)</label>
              <UInputNumber
                v-model="task.estimacion_horas"
                :min="0"
                size="sm"
                class="w-full"
              />
            </div>
          </div>
          <div>
            <label class="text-xs font-medium text-muted mb-1 block">Recordatorio</label>
            <ReminderForm
              :task-id="task.id"
              @saved="onReminderSaved"
            />
          </div>
          <!-- <div>
            <label class="text-xs font-medium text-muted mb-1 block">Etiquetas</label>
            <div class="flex flex-wrap gap-1.5">
              <UBadge
                v-for="tag in task.etiquetas || []"
                :key="tag.id"
                size="sm"
                color="info"
                variant="subtle"
              >
                {{ tag.nombre }}
              </UBadge>
              <UButton
                icon="i-lucide-plus"
                size="sm"
                color="neutral"
                variant="ghost"
                class="rounded-full"
                @click="addLabel = !addLabel"
              />
            </div>
            <LabelForm
              v-if="addLabel"
              :task-id="task.id"
              class="mt-3"
            />
          </div> -->
        </div>
      </div>

      <div v-if="activeTab === 'subtasks'">
        <div class="space-y-1">
          <div
            v-for="sub in subtasks"
            :key="sub.id"
            class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-(--ui-tertiary) transition-colors group"
          >
            <UCheckbox
              :model-value="sub.estado"
              @change="toggleSubtask(sub)"
            />
            <span
              class="text-sm flex-1"
              :class="sub.estado ? 'line-through text-muted' : ''"
            >{{ sub.titulo }}</span>
            <UButton
              icon="i-lucide-trash-2"
              size="xs"
              color="neutral"
              variant="ghost"
              class="opacity-0 group-hover:opacity-100"
              @click="removeSubtask(sub)"
            />
          </div>
        </div>
        <UButton
          label="Añadir subtarea"
          icon="i-lucide-plus"
          size="sm"
          color="neutral"
          variant="ghost"
          class="mt-3"
          @click="addSubtask = !addSubtask"
        />
        <SubtaskForm
          v-if="addSubtask"
          :task-id="task.id"
          class="mt-4"
          @saved="fetchSubtasks"
        />
      </div>

      <div v-if="activeTab === 'comments'">
        <div class="space-y-4">
          <div
            v-for="c in comments"
            :key="c.id"
            class="flex gap-2.5"
          >
            <UAvatar
              v-bind="avatarProps(c)"
              size="sm"
              class="shrink-0 mt-0.5"
            />
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <span class="text-sm font-medium">{{ c.usuario?.nombre || 'Desconocido' }}</span>
                <span class="text-[10px] text-muted">{{ formatDateTime(c.created_at) }}</span>
                <div class="ml-auto flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity" />
                <UButton
                  v-if="editCommentId !== c.id"
                  icon="i-lucide-pencil"
                  size="xs"
                  color="neutral"
                  variant="ghost"
                  @click="startEditComment(c)"
                />
                <UButton
                  v-if="editCommentId !== c.id"
                  icon="i-lucide-trash-2"
                  size="xs"
                  color="neutral"
                  variant="ghost"
                  @click="removeComment(c)"
                />
              </div>
              <p
                v-if="editCommentId !== c.id"
                class="text-sm text-default mt-0.5"
              >
                {{ c.comentario }}
              </p>
              <div
                v-else
                class="flex gap-2 mt-1"
              >
                <UInput
                  v-model="editCommentText"
                  size="sm"
                  class="flex-1"
                  @keydown.enter.prevent="saveEditComment"
                />
                <UButton
                  icon="i-lucide-check"
                  size="sm"
                  color="primary"
                  variant="solid"
                  @click="saveEditComment"
                />
              </div>
              <div class="mt-1.5">
                <ReactionPicker
                  :comment="c"
                  @changed="onReactionChanged"
                />
              </div>
            </div>
          </div>
        </div>
        <CommentForm
          class="mt-4"
          :task-id="task.id"
          @saved="onCommentSaved"
        />
      </div>

      <div v-if="activeTab === 'files'">
        <div class="space-y-2">
          <FilesTask
            :files="files"
            @removed="fetchFiles"
          />
        </div>
        <UploadFileForm
          class="mt-4"
          :task-id="task.id"
          @saved="fetchFiles"
        />
      </div>

      <div v-if="activeTab === 'history'">
        <div class="space-y-3">
          <div
            v-for="a in activity"
            :key="a.id"
            class="flex gap-2.5"
          >
            <UAvatar
              v-bind="avatarProps({ usuario: a.usuario })"
              size="xs"
              class="shrink-0 mt-0.5"
            />
            <div>
              <p class="text-sm">
                <span class="font-medium">{{ a.usuario?.nombre || 'Desconocido' }}</span>
                {{ a.descripcion }}
              </p>
              <span class="text-[10px] text-muted">{{ formatDateTime(a.created_at) }}</span>
            </div>
          </div>
          <div
            v-if="activity.length === 0"
            class="text-center text-sm text-muted py-8"
          >
            Sin actividad registrada
          </div>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-2 px-4 h-14 border-t border-default shrink-0">
      <UButton
        label="Guardar cambios"
        size="sm"
        color="primary"
        variant="solid"
        class="flex-1 justify-center"
        :loading="saving"
        :disabled="saving || deleting"
        @click="saveChanges"
      />
      <UButton
        icon="i-lucide-trash-2"
        size="sm"
        color="error"
        variant="ghost"
        class="px-10"
        :loading="deleting"
        :disabled="saving || deleting"
        @click="deleteTask"
      />
    </div>
  </div>
</template>
