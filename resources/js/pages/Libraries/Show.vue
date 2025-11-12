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
import { Plus, Pencil, Trash2, Package, ArrowLeft } from 'lucide-vue-next';

interface LibraryVersion {
    id: number;
    library_id: number;
    client_cnpj: string | null;
    machine_name: string | null;
    version: string;
    download_url_primary: string | null;
    download_url_secondary: string | null;
    active: boolean;
    notes: string | null;
    created_at: string;
    updated_at: string;
}

interface Library {
    id: number;
    system: string;
    name: string;
    description: string | null;
    active: boolean;
    versions: LibraryVersion[];
}

const props = defineProps<{
    library: Library;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Bibliotecas',
        href: '/libraries',
    },
    {
        title: props.library.name,
        href: `/libraries/${props.library.id}`,
    },
];

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const editingVersion = ref<LibraryVersion | null>(null);

const createForm = useForm({
    client_cnpj: '',
    machine_name: '',
    version: '',
    download_url_primary: '',
    download_url_secondary: '',
    active: true,
    notes: '',
});

const editForm = useForm({
    client_cnpj: '',
    machine_name: '',
    version: '',
    download_url_primary: '',
    download_url_secondary: '',
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
    editForm.machine_name = version.machine_name || '';
    editForm.version = version.version;
    editForm.download_url_primary = version.download_url_primary || '';
    editForm.download_url_secondary = version.download_url_secondary || '';
    editForm.active = version.active;
    editForm.notes = version.notes || '';
    isEditDialogOpen.value = true;
}

function submitCreate() {
    createForm.post(`/libraries/${props.library.id}/versions`, {
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
        },
    });
}

function submitEdit() {
    if (!editingVersion.value) return;

    editForm.put(
        `/libraries/${props.library.id}/versions/${editingVersion.value.id}`,
        {
            onSuccess: () => {
                isEditDialogOpen.value = false;
                editForm.reset();
                editingVersion.value = null;
            },
        }
    );
}

function deleteVersion(versionId: number) {
    if (confirm('Tem certeza que deseja excluir esta versão?')) {
        router.delete(`/libraries/${props.library.id}/versions/${versionId}`);
    }
}

function formatCNPJ(cnpj: string | null): string {
    if (!cnpj) return 'Versão Padrão';
    return cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
}

function getVersionLabel(version: LibraryVersion): string {
    if (!version.client_cnpj) return 'Versão Padrão';
    
    const cnpj = formatCNPJ(version.client_cnpj);
    
    if (version.machine_name) {
        return `${cnpj} - ${version.machine_name}`;
    }
    
    return cnpj;
}

function goBack() {
    router.visit('/libraries');
}
</script>

<template>
    <Head :title="`${library.name} - Versões`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" @click="goBack">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <div class="text-sm text-muted-foreground font-semibold">
                            {{ library.system }}
                        </div>
                        <h1 class="text-3xl font-bold tracking-tight">
                            {{ library.name }}
                        </h1>
                        <p class="text-muted-foreground">
                            {{ library.description || 'Gerencie as versões desta biblioteca' }}
                        </p>
                    </div>
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
                                <Label for="create-machine">Nome da Máquina (opcional)</Label>
                                <Input
                                    id="create-machine"
                                    v-model="createForm.machine_name"
                                    placeholder="Ex: PDV01, CAIXA-01"
                                    maxlength="100"
                                />
                                <p class="text-sm text-muted-foreground">
                                    Para versão específica de uma máquina
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
                                <Label for="create-url-primary">
                                    URL Download Primária
                                    <span class="text-xs text-muted-foreground ml-1">(opcional)</span>
                                </Label>
                                <Input
                                    id="create-url-primary"
                                    v-model="createForm.download_url_primary"
                                    type="url"
                                    placeholder="https://exemplo.com/biblioteca-v9.8.0.9.zip"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Link principal para download da biblioteca
                                </p>
                            </div>
                            <div class="grid gap-2">
                                <Label for="create-url-secondary">
                                    URL Download Secundária
                                    <span class="text-xs text-muted-foreground ml-1">(opcional)</span>
                                </Label>
                                <Input
                                    id="create-url-secondary"
                                    v-model="createForm.download_url_secondary"
                                    type="url"
                                    placeholder="https://backup.exemplo.com/biblioteca-v9.8.0.9.zip"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Link alternativo caso o primário esteja indisponível
                                </p>
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

            <!-- Versões Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="version in library.versions"
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
                                        {{ getVersionLabel(version) }}
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
                            
                            <!-- URLs de Download -->
                            <div v-if="version.download_url_primary || version.download_url_secondary" class="space-y-2 rounded-md bg-muted/30 p-3">
                                <div class="text-xs font-semibold text-muted-foreground uppercase">
                                    📥 Downloads Disponíveis
                                </div>
                                <div v-if="version.download_url_primary" class="flex items-start gap-2">
                                    <span class="text-xs font-medium text-primary">Primário:</span>
                                    <a 
                                        :href="version.download_url_primary" 
                                        target="_blank"
                                        class="text-xs text-blue-600 hover:underline break-all"
                                    >
                                        {{ version.download_url_primary }}
                                    </a>
                                </div>
                                <div v-if="version.download_url_secondary" class="flex items-start gap-2">
                                    <span class="text-xs font-medium text-muted-foreground">Secundário:</span>
                                    <a 
                                        :href="version.download_url_secondary" 
                                        target="_blank"
                                        class="text-xs text-blue-600 hover:underline break-all"
                                    >
                                        {{ version.download_url_secondary }}
                                    </a>
                                </div>
                            </div>
                            
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
                v-if="library.versions.length === 0"
                class="flex min-h-[400px] flex-col items-center justify-center rounded-lg border border-dashed p-8 text-center"
            >
                <Package class="h-12 w-12 text-muted-foreground" />
                <h3 class="mt-4 text-lg font-semibold">
                    Nenhuma versão cadastrada
                </h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Adicione a primeira versão para {{ library.name }}
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
                            <Label for="edit-machine">Nome da Máquina (opcional)</Label>
                            <Input
                                id="edit-machine"
                                v-model="editForm.machine_name"
                                placeholder="Ex: PDV01, CAIXA-01"
                                maxlength="100"
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
                            <Label for="edit-url-primary">
                                URL Download Primária
                                <span class="text-xs text-muted-foreground ml-1">(opcional)</span>
                            </Label>
                            <Input
                                id="edit-url-primary"
                                v-model="editForm.download_url_primary"
                                type="url"
                                placeholder="https://exemplo.com/biblioteca-v9.8.0.9.zip"
                            />
                            <p class="text-xs text-muted-foreground">
                                Link principal para download da biblioteca
                            </p>
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-url-secondary">
                                URL Download Secundária
                                <span class="text-xs text-muted-foreground ml-1">(opcional)</span>
                            </Label>
                            <Input
                                id="edit-url-secondary"
                                v-model="editForm.download_url_secondary"
                                type="url"
                                placeholder="https://backup.exemplo.com/biblioteca-v9.8.0.9.zip"
                            />
                            <p class="text-xs text-muted-foreground">
                                Link alternativo caso o primário esteja indisponível
                            </p>
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
