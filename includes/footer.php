        </div>

        <!-- Global confirmation modal (hidden, reused for delete/edit confirmations) -->
        <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmModalLabel">Confirmação</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="confirmModalMessage" class="mb-0">Tem certeza que deseja prosseguir?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                        <button type="button" id="confirmModalYes" class="btn btn-primary btn-sm">OK</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message modal for success/info/warning messages (reused) -->
        <div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <div class="modal-content border-0">
                    <div class="modal-body text-center py-4">
                        <div id="messageModalIcon" class="mb-2"></div>
                        <p id="messageModalMessage" class="mb-0 font-weight-medium"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remote modal: used to load add/edit forms via AJAX into the page -->
        <div class="modal fade" id="remoteModal" tabindex="-1" role="dialog" aria-labelledby="remoteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="remoteModalLabel">Formulário</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="remoteModalBody">
                        <!-- conteúdo carregado via AJAX -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="remoteModalCancel" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" id="remoteModalSave" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="/SystemContracts/js/script.js"></script>
</body>
</html>
