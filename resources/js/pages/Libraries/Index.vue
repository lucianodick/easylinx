<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Pencil, Trash2, Library as LibraryIcon, Eye } from 'lucide-vue-next';

interface Library {
    id: number;
    system: string;
    name: string;
    description: string | null;
    active: boolean;
    versions_count: number;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    libraries: Library[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Bibliotecas',
        href: '/libraries',
    },
];

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const editingLibrary = ref<Library | null>(null);

const createForm = useForm({
    system: '',
    name: '',
    description: '',
    active: true,
});

const editForm = useForm({
    system: '',
    name: '',
    description: '',
    active: true,
});

function openCreateDialog() {
    createForm.reset();
    isCreateDialogOpen.value = true;
}

function openEditDialog(library: Library) {
    editingLibrary.value = library;
    editForm.system = library.system;
    editForm.name = library.name;
    editForm.description = library.description || '';
    editForm.active = library.active;
    isEditDialogOpen.value = true;
}

function submitCreate() {
    createForm.post('/libraries', {
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
        },
    });
}

function submitEdit() {
    if (!editingLibrary.value) return;

    editForm.put(`/libraries/${editingLibrary.value.id}`, {
        onSuccess: () => {
            isEditDialogOpen.value = false;
            editForm.reset();
            editingLibrary.value = null;
        },
    });
}

function deleteLibrary(id: number) {
    if (confirm('Tem certeza que deseja excluir esta biblioteca?')) {
        router.delete(`/libraries/${id}`);
    }
}
</script>

<template>
    <Head title="Bibliotecas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Bibliotecas</h1>
                    <p class="text-muted-foreground">
                        Gerencie as bibliotecas e suas versões
                    </p>
                </div>
                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button @click="openCreateDialog">
                            <Plus class="mr-2 h-4 w-4" />
                            Nova Biblioteca
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Adicionar Nova Biblioteca</DialogTitle>
                            <DialogDescription>
                                Cadastre uma nova biblioteca fiscal
                            </DialogDescription>
                        </DialogHeader>
                        <div class="grid gap-4 py-4">
                            <div class="grid gap-2">
                                <Label for="create-system">Sistema *</Label>
                                <Input
                                    id="create-system"
                                    v-model="createForm.system"
                                    placeholder="Ex: SETA, Easylinx"
                                    required
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="create-name">Nome *</Label>
                                <Input
                                    id="create-name"
                                    v-model="createForm.name"
                                    placeholder="Ex: Fiscal Flow, Tecnospeed, Uninfe"
                                    required
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="create-description">Descrição</Label>
                                <Input
                                    id="create-description"
                                    v-model="createForm.description"
                                    placeholder="Descrição da biblioteca"
                                />
                            </div>
                            <div class="flex items-center gap-2">
                                <input
                                    id="create-active"
                                    v-model="createForm.active"
                                    type="checkbox"
                                    class="h-4 w-4"
                                />
                                <Label for="create-active">Ativa</Label>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button
                                variant="outline"
                                @click="isCreateDialogOpen = false"
                            >
                                Cancelar
                            </Button>
                            <Button
                                @click="submitCreate"
                                :disabled="createForm.processing"
                            >
                                Criar
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Libraries Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="library in props.libraries"
                    :key="library.id"
                    :class="{ 'opacity-60': !library.active }"
                >
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2">
                                <LibraryIcon class="h-5 w-5 text-primary" />
                                <div>
                                    <CardTitle class="text-xl">
                                        {{ library.name }}
                                    </CardTitle>
                                    <CardDescription class="mt-1">
                                        <div class="font-semibold text-primary">{{ library.system }}</div>
                                        {{ library.versions_count }}
                                        {{ library.versions_count === 1 ? 'versão' : 'versões' }}
                                    </CardDescription>
                                </div>
                            </div>
                            <Badge
                                :variant="library.active ? 'default' : 'secondary'"
                            >
                                {{ library.active ? 'Ativa' : 'Inativa' }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <p
                                v-if="library.description"
                                class="text-sm text-muted-foreground"
                            >
                                {{ library.description }}
                            </p>
                            <div class="flex gap-2 pt-2">
                                <Link :href="`/libraries/${library.id}`">
                                    <Button variant="default" size="sm">
                                        <Eye class="mr-1 h-3 w-3" />
                                        Versões
                                    </Button>
                                </Link>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="openEditDialog(library)"
                                >
                                    <Pencil class="mr-1 h-3 w-3" />
                                    Editar
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="deleteLibrary(library.id)"
                                >
                                    <Trash2 class="mr-1 h-3 w-3" />
                                    Excluir
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div
                v-if="props.libraries.length === 0"
                class="flex min-h-[400px] flex-col items-center justify-center rounded-lg border border-dashed p-8 text-center"
            >
                <LibraryIcon class="h-12 w-12 text-muted-foreground" />
                <h3 class="mt-4 text-lg font-semibold">Nenhuma biblioteca cadastrada</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Adicione a primeira biblioteca fiscal
                </p>
                <Button class="mt-4" @click="openCreateDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    Nova Biblioteca
                </Button>
            </div>

            <!-- Edit Dialog -->
            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Editar Biblioteca</DialogTitle>
                        <DialogDescription>
                            Modifique os dados da biblioteca
                        </DialogDescription>
                    </DialogHeader>
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="edit-system">Sistema *</Label>
                            <Input
                                id="edit-system"
                                v-model="editForm.system"
                                placeholder="Ex: SETA, Easylinx"
                                required
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-name">Nome *</Label>
                            <Input
                                id="edit-name"
                                v-model="editForm.name"
                                placeholder="Nome da biblioteca"
                                required
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-description">Descrição</Label>
                            <Input
                                id="edit-description"
                                v-model="editForm.description"
                                placeholder="Descrição da biblioteca"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <input
                                id="edit-active"
                                v-model="editForm.active"
                                type="checkbox"
                                class="h-4 w-4"
                            />
                            <Label for="edit-active">Ativa</Label>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" @click="isEditDialogOpen = false">
                            Cancelar
                        </Button>
                        <Button
                            @click="submitEdit"
                            :disabled="editForm.processing"
                        >
                            Salvar
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
