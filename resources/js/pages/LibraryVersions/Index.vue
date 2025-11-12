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
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Pencil, Trash2, Package } from 'lucide-vue-next';

interface LibraryVersion {
    id: number;
    client_cnpj: string | null;
    version: string;
    active: boolean;
    notes: string | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    versions: LibraryVersion[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Versões da Biblioteca',
        href: '/library-versions',
    },
];

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const editingVersion = ref<LibraryVersion | null>(null);

const createForm = useForm({
    client_cnpj: '',
    version: '',
    active: true,
    notes: '',
});

const editForm = useForm({
    client_cnpj: '',
    version: '',
    active: true,
    notes: '',
});

function openCreateDialog() {
    createForm.reset();
    isCreateDialogOpen.value = true;
}

function openEditDialog(version: LibraryVersion) {
    editingVersion.value = version;
    editForm.client_cnpj = version.client_cnpj || '';
    editForm.version = version.version;
    editForm.active = version.active;
    editForm.notes = version.notes || '';
    isEditDialogOpen.value = true;
}

function submitCreate() {
    createForm.post('/library-versions', {
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
        },
    });
}

function submitEdit() {
    if (!editingVersion.value) return;

    editForm.put(`/library-versions/${editingVersion.value.id}`, {
        onSuccess: () => {
            isEditDialogOpen.value = false;
            editForm.reset();
            editingVersion.value = null;
        },
    });
}

function deleteVersion(id: number) {
    if (confirm('Tem certeza que deseja excluir esta versão?')) {
        router.delete(`/library-versions/${id}`);
    }
}

function formatCNPJ(cnpj: string | null): string {
    if (!cnpj) return 'Versão Padrão';
    return cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
}
</script>

<template>
    <Head title="Versões da Biblioteca" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Versões da Biblioteca</h1>
                    <p class="text-muted-foreground">
                        Gerencie versões da biblioteca por cliente
                    </p>
                </div>
                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button @click="openCreateDialog">
                            <Plus class="mr-2 h-4 w-4" />
                            Nova Versão
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Adicionar Nova Versão</DialogTitle>
                            <DialogDescription>
                                Deixe o CNPJ vazio para definir a versão padrão
                            </DialogDescription>
                        </DialogHeader>
                        <div class="grid gap-4 py-4">
                            <div class="grid gap-2">
                                <Label for="create-cnpj">CNPJ do Cliente (opcional)</Label>
                                <Input
                                    id="create-cnpj"
                                    v-model="createForm.client_cnpj"
                                    placeholder="00.000.000/0000-00"
                                    maxlength="18"
                                />
                                <p class="text-sm text-muted-foreground">
                                    Deixe vazio para versão padrão
                                </p>
                            </div>
                            <div class="grid gap-2">
                                <Label for="create-version">Versão *</Label>
                                <Input
                                    id="create-version"
                                    v-model="createForm.version"
                                    placeholder="9.8.0.9"
                                    required
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="create-notes">Observações</Label>
                                <Input
                                    id="create-notes"
                                    v-model="createForm.notes"
                                    placeholder="Notas sobre esta versão"
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

            <!-- Versões List -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="version in props.versions"
                    :key="version.id"
                    :class="{
                        'border-primary': version.client_cnpj === null,
                        'opacity-60': !version.active,
                    }"
                >
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2">
                                <Package class="h-5 w-5 text-primary" />
                                <div>
                                    <CardTitle class="text-xl">
                                        {{ version.version }}
                                    </CardTitle>
                                    <CardDescription class="mt-1">
                                        {{ formatCNPJ(version.client_cnpj) }}
                                    </CardDescription>
                                </div>
                            </div>
                            <Badge
                                :variant="version.active ? 'default' : 'secondary'"
                            >
                                {{ version.active ? 'Ativa' : 'Inativa' }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <p
                                v-if="version.notes"
                                class="text-sm text-muted-foreground"
                            >
                                {{ version.notes }}
                            </p>
                            <div
                                v-if="version.client_cnpj === null"
                                class="rounded-md bg-primary/10 p-2 text-sm"
                            >
                                🌟 Versão padrão para todos os clientes
                            </div>
                            <div class="flex gap-2 pt-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="openEditDialog(version)"
                                >
                                    <Pencil class="mr-1 h-3 w-3" />
                                    Editar
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="deleteVersion(version.id)"
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
                v-if="props.versions.length === 0"
                class="flex min-h-[400px] flex-col items-center justify-center rounded-lg border border-dashed p-8 text-center"
            >
                <Package class="h-12 w-12 text-muted-foreground" />
                <h3 class="mt-4 text-lg font-semibold">Nenhuma versão cadastrada</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Adicione a primeira versão da biblioteca
                </p>
                <Button class="mt-4" @click="openCreateDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    Nova Versão
                </Button>
            </div>

            <!-- Edit Dialog -->
            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Editar Versão</DialogTitle>
                        <DialogDescription>
                            Modifique os dados da versão
                        </DialogDescription>
                    </DialogHeader>
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="edit-cnpj">CNPJ do Cliente (opcional)</Label>
                            <Input
                                id="edit-cnpj"
                                v-model="editForm.client_cnpj"
                                placeholder="00.000.000/0000-00"
                                maxlength="18"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-version">Versão *</Label>
                            <Input
                                id="edit-version"
                                v-model="editForm.version"
                                placeholder="9.8.0.9"
                                required
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-notes">Observações</Label>
                            <Input
                                id="edit-notes"
                                v-model="editForm.notes"
                                placeholder="Notas sobre esta versão"
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
