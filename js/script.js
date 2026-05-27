// Input mask initializers using jQuery namespaced events
window.initMasks = function() {
    // Phone
    $(document).off('input.mask', '[data-mask="phone"]').on('input.mask', '[data-mask="phone"]', function() {
        var value = $(this).val().replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d)/, '$1-$2');
        }
        $(this).val(value);
    });

    // CPF/CNPJ
    $(document).off('input.mask', '[data-mask="cpf"]').on('input.mask', '[data-mask="cpf"]', function() {
        var value = $(this).val().replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{2})$/, '$1-$2');
        }
        $(this).val(value);
    });

    // CEP
    $(document).off('input.mask', '[data-mask="cep"]').on('input.mask', '[data-mask="cep"]', function() {
        var value = $(this).val().replace(/\D/g, '');
        value = value.replace(/(\d{5})(\d{3})/, '$1-$2');
        $(this).val(value);
    });

    // Currency
    $(document).off('input.mask', '[data-mask="currency"]').on('input.mask', '[data-mask="currency"]', function() {
        var value = $(this).val().replace(/\D/g, '');
        value = (value / 100).toFixed(2);
        value = value.replace('.', ',').replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        $(this).val('R$ ' + value);
    });
};

// Initialize masks now
if (window.jQuery) { $(document).ready(function() { window.initMasks(); }); }

// Função para confirmação de exclusão
// Função para confirmação de exclusão compatível (usa modal)
function confirmarExclusao(id, nome) {
    var message = 'Tem certeza que deseja excluir: ' + nome + '?';
    $('#confirmModalMessage').text(message);
    $('#confirmModal').modal('show');
    $('#confirmModalYes').off('click').on('click', function() {
        $('#confirmModal').modal('hide');
        window.location.href = '?action=delete&id=' + encodeURIComponent(id);
    });
}

// Delegation: any element with `data-confirm` will open the modal. Use `data-href` to navigate or `data-form` to submit a form selector.
$(document).on('click', '[data-confirm]', function(e) {
    e.preventDefault();
    var $el = $(this);
    var message = $el.data('confirm') || 'Tem certeza que deseja prosseguir?';
    var href = $el.data('href') || $el.attr('href');
    var formSelector = $el.data('form');

    $('#confirmModalMessage').text(message);
    $('#confirmModal').modal('show');
    $('#confirmModalYes').off('click').on('click', function() {
        $('#confirmModal').modal('hide');
        if (formSelector) {
            $(formSelector).submit();
        } else if (href && href !== '#' && typeof href !== 'undefined') {
            window.location.href = href;
        }
    });
});

// Validação de formulário
function validarFormulario(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }
}

// Fechar alerta automaticamente (se houver)
$(document).ready(function() {
    $('.alert').each(function() {
        var $a = $(this);
        setTimeout(function() { $a.fadeOut(300); }, 5000);
    });
});

// Mostrar modal de mensagem se existir #flashMessage (renderizado pelo servidor)
$(document).ready(function() {
    var $flash = $('#flashMessage');
    if ($flash.length) {
        var tipo = $flash.data('tipo') || 'info';
        var mensagem = $flash.data('mensagem') || '';

        // Ajustar ícone/estilo conforme tipo
        var $icon = $('#messageModalIcon');
        var $msg = $('#messageModalMessage');
        $msg.text(mensagem);
        $icon.html('');
        if (tipo === 'sucesso') {
            $icon.html('<i class="fas fa-check-circle fa-2x text-success"></i>');
        } else if (tipo === 'erro') {
            $icon.html('<i class="fas fa-times-circle fa-2x text-danger"></i>');
        } else if (tipo === 'aviso') {
            $icon.html('<i class="fas fa-exclamation-circle fa-2x text-warning"></i>');
        } else {
            $icon.html('<i class="fas fa-info-circle fa-2x text-info"></i>');
        }

        $('#messageModal').modal('show');

        // Auto fechar em 4s
        setTimeout(function() {
            $('#messageModal').modal('hide');
        }, 4000);
    }
});

    // Carregar formulários remotos (data-remote-url) dentro do #remoteModal
    $(document).on('click', '[data-remote-url]', function(e) {
        e.preventDefault();
        var url = $(this).data('remote-url');
        var title = $(this).data('remote-title') || '';
        if (!url) return;

        $('#remoteModalLabel').text(title || 'Formulário');
        $('#remoteModalBody').html('<div class="text-center py-4">Carregando...</div>');
        $('#remoteModal').modal('show');

        $.get(url, function(html) {
            $('#remoteModalBody').html(html);
            // Re-run any masks/initializers (simple):
            if (window.initMasks) try { window.initMasks(); } catch(e) {}
        }).fail(function() {
            $('#remoteModalBody').html('<div class="alert alert-danger">Erro ao carregar o formulário.</div>');
        });
    });

        // Intercept form submits inside the remote modal and send as AJAX
        $(document).on('submit', '#remoteModalBody form', function(e) {
            e.preventDefault();
            var $form = $(this);
            var url = $form.attr('action') || window.location.href;
            var data = $form.serialize() + '&ajax=1';

            // Disable submit button
            var $btn = $form.find('button[type=submit]');
            $btn.prop('disabled', true).addClass('disabled');

            $.post(url, data, function(resp) {
                try {
                    var json = (typeof resp === 'object') ? resp : JSON.parse(resp);
                    if (json.success) {
                        $('#remoteModal').modal('hide');
                        // show message modal
                        $('#messageModalIcon').html('<i class="fas fa-check-circle fa-2x text-success"></i>');
                        $('#messageModalMessage').text(json.message || 'Operação realizada.');
                        $('#messageModal').modal('show');
                        setTimeout(function() { $('#messageModal').modal('hide'); }, 3000);
                        // optionally reload to update list
                        if (json.redirect) {
                            setTimeout(function() { window.location.href = json.redirect; }, 800);
                        } else {
                            setTimeout(function() { location.reload(); }, 800);
                        }
                    } else {
                        // show error inside modal
                        var err = json.message || 'Erro';
                        $form.prepend('<div class="alert alert-danger">'+err+'</div>');
                    }
                } catch (e) {
                    $form.prepend('<div class="alert alert-danger">Resposta inválida do servidor.</div>');
                }
            }).fail(function() {
                $form.prepend('<div class="alert alert-danger">Erro de rede ao enviar o formulário.</div>');
            }).always(function() {
                $btn.prop('disabled', false).removeClass('disabled');
            });
        });

// --- Two-step modal flow for Cliente (step1 -> step2) and CEP lookup ---

// Helper: lookup CEP using ViaCEP
function buscarCep(cep, cb) {
    if (!cep) return cb(null);
    cep = cep.toString().replace(/\D/g, '');
    if (cep.length !== 8) return cb(null);
    var url = 'https://viacep.com.br/ws/' + cep + '/json/';
    $.getJSON(url).done(function(data) {
        if (data && !data.erro) cb(data);
        else cb(null);
    }).fail(function() { cb(null); });
}

// When clicking Próximo on step1, store data and load step2
$(document).on('click', '#clienteStep1Next', function(e) {
    e.preventDefault();
    var $form = $('#clienteStep1Form');
    if ($form.length === 0) return;
    // basic validation
    if (!$form[0].checkValidity()) {
        $form.addClass('was-validated');
        return;
    }
    var step1Array = $form.serializeArray();
    $('#remoteModal').data('cliente-step1', step1Array);

    // load step2 partial
    $('#remoteModalBody').html('<div class="text-center py-4">Carregando etapa de endereço...</div>');
    $.get('/SystemContracts/clientes/form_step2.php', function(html) {
        $('#remoteModalBody').html(html);
        if (window.initMasks) try { window.initMasks(); } catch(e) {}
        // populate fields from step1 if any (e.g., nome) into hidden or visible fields
        var stored = $('#remoteModal').data('cliente-step1') || [];
        // Keep stored on modal for final submit
        $('#remoteModal').data('cliente-step1', stored);
        // If step2 form wants to display some step1 values, copy them
        var $step2 = $('#remoteModalBody').find('#clienteStep2Form');
        if ($step2.length) {
            $.each(stored, function(i, kv) {
                var name = kv.name, val = kv.value;
                var $field = $step2.find('[name="'+name+'"]');
                if ($field.length) $field.val(val);
            });
        }
        // focus CEP
        $('#cep').focus();
    }).fail(function() {
        $('#remoteModalBody').html('<div class="alert alert-danger">Erro ao carregar etapa de endereço.</div>');
    });
});

// Back button on step2 -> reload step1 and populate
$(document).on('click', '#clienteStep2Back', function(e) {
    e.preventDefault();
    var stored = $('#remoteModal').data('cliente-step1') || [];
    $('#remoteModalBody').html('<div class="text-center py-4">Voltando para etapa anterior...</div>');
    $.get('/SystemContracts/clientes/form_step1.php', function(html) {
        $('#remoteModalBody').html(html);
        if (window.initMasks) try { window.initMasks(); } catch(e) {}
        var $step1 = $('#remoteModalBody').find('#clienteStep1Form');
        if ($step1.length) {
            $.each(stored, function(i, kv) {
                $step1.find('[name="'+kv.name+'"]').val(kv.value);
            });
        }
    }).fail(function() {
        $('#remoteModalBody').html('<div class="alert alert-danger">Erro ao carregar etapa anterior.</div>');
    });
});

// CEP lookup triggers
$(document).on('click', '#buscarCepBtn', function(e) {
    e.preventDefault();
    var cep = $('#cep').val() || '';
    cep = cep.replace(/\D/g, '');
    if (!cep) return;
    buscarCep(cep, function(data) {
        if (data) {
            var endereco = (data.logradouro || '') + (data.bairro ? ' - ' + data.bairro : '') + (data.complemento ? ' ' + data.complemento : '');
            $('#endereco').val(endereco.trim());
            $('#cidade').val(data.localidade || '');
            $('#estado').val(data.uf || '');
        } else {
            showMessageModal('CEP', 'CEP não encontrado ou inválido.');
        }
    });
});

function mostrarMensagemModal(tipo, mensagem) {
    var $icon = $('#messageModalIcon');
    var $msg = $('#messageModalMessage');
    $msg.text(mensagem || '');
    $icon.html('');
    if (tipo === 'sucesso') {
        $icon.html('<i class="fas fa-check-circle fa-2x text-success"></i>');
    } else if (tipo === 'erro') {
        $icon.html('<i class="fas fa-times-circle fa-2x text-danger"></i>');
    } else if (tipo === 'aviso') {
        $icon.html('<i class="fas fa-exclamation-circle fa-2x text-warning"></i>');
    } else {
        $icon.html('<i class="fas fa-info-circle fa-2x text-info"></i>');
    }
    $('#messageModal').modal('show');
}

function atualizarValorTotalContrato() {
    var total = 0;
    $('#cobrancasList .cobranca-valor').each(function() {
        var valor = parseFloat(String($(this).val() || '0').replace(',', '.'));
        if (!isNaN(valor)) total += valor;
    });
    var $valorTotal = $('#valor_total');
    if ($valorTotal.length) {
        $valorTotal.val(total.toFixed(2));
    }
    return total;
}

// Auto lookup on CEP blur
$(document).on('blur', '#cep', function() {
    var cep = $(this).val().replace(/\D/g, '');
    if (!cep) return;
    if (cep.length === 8) {
        buscarCep(cep, function(data) {
            if (data) {
                var endereco = (data.logradouro || '') + (data.bairro ? ' - ' + data.bairro : '') + (data.complemento ? ' ' + data.complemento : '');
                $('#endereco').val(endereco.trim());
                $('#cidade').val(data.localidade || '');
                $('#estado').val(data.uf || '');
            }
        });
    }
});

// Modify AJAX submit handler to merge step1 data if present
// Add/remove cobrancas UI
$(document).on('click', '#adicionarCobranca', function(e) {
    e.preventDefault();
    var tpl = document.getElementById('cobrancaTemplate');
    if (!tpl) return;
    var clone = tpl.content.cloneNode(true);
    $('#cobrancasList').append(clone);
    atualizarValorTotalContrato();
});

$(document).on('click', '.removerCobranca', function(e) {
    e.preventDefault();
    $(this).closest('.cobranca-item').remove();
    atualizarValorTotalContrato();
});

$(document).on('input change', '#cobrancasList .cobranca-valor, #cobrancasList .cobranca-tipo, #cobrancasList .cobranca-descricao', function() {
    atualizarValorTotalContrato();
});

// Auto-preencher valor e descrição ao selecionar um produto no contrato
$(document).on('change', '.cobranca-produto', function() {
    var $option = $(this).find('option:selected');
    var preco = $option.data('preco');
    var nome = $option.text().trim();
    var $row = $(this).closest('.cobranca-item');
    
    if (preco) $row.find('.cobranca-valor').val(parseFloat(preco).toFixed(2));
    if (nome && nome !== 'Nenhum') $row.find('.cobranca-descricao').val(nome);
    atualizarValorTotalContrato();
});

$(document).ready(function() {
    atualizarValorTotalContrato();
});

$(document).off('submit', '#remoteModalBody form').on('submit', '#remoteModalBody form', function(e) {
    e.preventDefault();
    var $form = $(this);
    var url = $form.attr('action') || window.location.href;
    var step1 = $('#remoteModal').data('cliente-step1');

    var hasFile = $form.find('input[type=file]').length > 0 && $form.find('input[type=file]')[0].files.length > 0;
    var $btn = $form.find('button[type=submit]');
    $btn.prop('disabled', true).addClass('disabled');

    atualizarValorTotalContrato();

    if (hasFile) {
        var formData = new FormData($form[0]);
        formData.append('ajax', '1');
        // merge step1 if present
        if (step1 && Array.isArray(step1) && step1.length) {
            $.each(step1, function(i, kv) { formData.append(kv.name, kv.value); });
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(resp) {
                try {
                    var json = (typeof resp === 'object') ? resp : JSON.parse(resp);
                    if (json.success) {
                        $('#remoteModal').modal('hide');
                        mostrarMensagemModal('sucesso', json.message || 'Operação realizada.');
                        setTimeout(function() { $('#messageModal').modal('hide'); }, 3000);
                        if (json.redirect) {
                            setTimeout(function() { window.location.href = json.redirect; }, 800);
                        } else {
                            setTimeout(function() { location.reload(); }, 800);
                        }
                    } else {
                        mostrarMensagemModal('erro', json.message || 'Erro ao salvar contrato.');
                    }
                } catch (e) {
                    mostrarMensagemModal('erro', 'Resposta inválida do servidor.');
                }
            },
            error: function(xhr) {
                var texto = 'Erro de rede ao enviar o formulário.';
                if (xhr && xhr.responseText) {
                    var amostra = String(xhr.responseText).replace(/\s+/g, ' ').trim();
                    if (amostra) {
                        texto = amostra.substring(0, 180);
                    }
                }
                mostrarMensagemModal('erro', texto);
            },
            complete: function() { $btn.prop('disabled', false).removeClass('disabled'); }
        });
    } else {
        var data = '';
        if (step1 && Array.isArray(step1) && step1.length) {
            data = $.param(step1) + '&' + $form.serialize() + '&ajax=1';
        } else {
            data = $form.serialize() + '&ajax=1';
        }
        $.post(url, data, function(resp) {
            try {
                var json = (typeof resp === 'object') ? resp : JSON.parse(resp);
                if (json.success) {
                    $('#remoteModal').modal('hide');
                    mostrarMensagemModal('sucesso', json.message || 'Operação realizada.');
                    setTimeout(function() { $('#messageModal').modal('hide'); }, 3000);
                    if (json.redirect) {
                        setTimeout(function() { window.location.href = json.redirect; }, 800);
                    } else {
                        setTimeout(function() { location.reload(); }, 800);
                    }
                } else {
                    mostrarMensagemModal('erro', json.message || 'Erro ao salvar contrato.');
                }
            } catch (e) {
                mostrarMensagemModal('erro', 'Resposta inválida do servidor.');
            }
        }).fail(function() {
            mostrarMensagemModal('erro', 'Erro de rede ao enviar o formulário.');
        }).always(function() {
            $btn.prop('disabled', false).removeClass('disabled');
        });
    }
});

        // Footer Save button: submit the first form inside remoteModalBody
        $(document).on('click', '#remoteModalSave', function(e) {
            e.preventDefault();
            var $form = $('#remoteModalBody').find('form').first();
            if ($form.length) {
                $form.submit();
            } else {
                $('#remoteModal').modal('hide');
            }
        });

        // Contracts list search: run only on form submit (click Filtrar or Enter)
        $(document).on('submit', '#contratosSearchForm', function(e) {
            e.preventDefault();
            var termo = String($('#contratosSearch').val() || '').toLowerCase().trim();
            $('#contratosTable tbody tr').each(function() {
                var $tr = $(this);
                var numero = $tr.find('td').eq(0).text().toLowerCase();
                var cliente = $tr.find('td').eq(1).text().toLowerCase();
                var status = $tr.find('td').eq(3).text().toLowerCase();
                var text = (numero + ' ' + cliente + ' ' + status).replace(/\s+/g, ' ');
                $tr.toggle(!termo || text.indexOf(termo) !== -1);
            });
        });

// Quitação: load debts and handle payments
$(document).on('click', '.quitacao-cliente', function(e) {
    e.preventDefault();
    var clienteId = $(this).data('client-id');
    $('.quitacao-cliente').removeClass('active');
    $(this).addClass('active');
    loadQuitacaoDetalhes(clienteId);
});

$(document).on('input', '#quitacaoSearch', function() {
    var termo = String($(this).val() || '').toLowerCase().trim();
    $('.quitacao-cliente').each(function() {
        var nome = String($(this).data('cliente-nome') || $(this).text()).toLowerCase();
        $(this).toggle(!termo || nome.indexOf(termo) !== -1);
    });
});

$(document).ready(function() {
    var $firstCliente = $('.quitacao-cliente').first();
    if ($firstCliente.length) {
        $firstCliente.trigger('click');
    }
});

function loadQuitacaoDetalhes(clienteId) {
    $('#quitacaoDetalhe').html('<div class="text-center py-5 text-muted">Carregando...</div>');
    $.getJSON('/SystemContracts/pagamentos/quitacao_detalhes.php', { cliente_id: clienteId }, function(resp) {
        if (!resp.success) {
            $('#quitacaoDetalhe').html('<div class="text-danger p-3">'+resp.message+'</div>');
            return;
        }
        var items = resp.data;
        if (!items.length) {
            $('#quitacaoDetalhe').html('<div class="text-center py-5 text-muted"><i class="fas fa-check-circle fa-3x mb-3 text-success"></i><br>Tudo em dia! Nenhuma pendência encontrada.</div>');
            return;
        }
        var html = [];
        html.push('<div class="pdv-header d-flex justify-content-between align-items-end">');
        html.push('<div><h4 class="mb-0 font-weight-bold text-dark">Checkout de Dívidas</h4><p class="text-muted mb-0 small">Selecione os itens para realizar o recebimento</p></div>');
        html.push('<div class="text-right"><span class="badge badge-primary px-3 py-2" style="border-radius:8px">' + items.length + ' Itens</span></div>');
        html.push('</div>');
        html.push('<div class="pdv-body"><div class="table-responsive"><table class="table">');
        html.push('<thead><tr><th width="50"><input type="checkbox" id="quitacaoCheckAll"></th><th>Contrato</th><th>Descrição</th><th>Vencimento</th><th class="text-right">Valor Total</th></tr></thead>');
        html.push('<tbody>');
        items.forEach(function(it) {
            html.push('<tr data-id="'+it.id+'">');
            html.push('<td data-label="Sel."><input type="checkbox" class="quitacaoCheckbox" data-valor="'+it.valor+'"></td>');
            html.push('<td data-label="Contrato"><span class="text-primary font-weight-bold">'+(it.numero_contrato ? escapeHtml(it.numero_contrato) : '-')+'</span></td>');
            html.push('<td data-label="Descrição">'+escapeHtml(it.descricao || it.tipo || 'Pagamento')+'</td>');
            html.push('<td data-label="Vencimento">'+(it.vencimento || '-')+'</td>');
            html.push('<td data-label="Valor" class="text-right font-weight-bold text-dark" style="font-size:1.1rem">R$ '+parseFloat(it.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})+'</td>');
            html.push('</tr>');
        });
        html.push('</tbody></table></div></div>');
        
        html.push('<div class="pdv-footer">');
        html.push('<div class="total-banner py-3">');
        html.push('<div><span class="d-block small text-uppercase font-weight-bold mb-1" style="letter-spacing:1px; opacity:0.7">Total Selecionado</span><span class="h1 font-weight-bold mb-0" id="quitacaoTotal">R$ 0,00</span></div>');
        html.push('<button class="btn btn-success btn-lg px-5 py-3 shadow-lg font-weight-bold" id="quitacaoPagarSelecionados" style="border-radius:12px; font-size:1.2rem"><i class="fas fa-money-bill-wave mr-2"></i> FECHAR PEDIDO</button>');
        html.push('</div></div>');

        $('#quitacaoDetalhe').html(html.join(''));
    }).fail(function() {
        $('#quitacaoDetalhe').html('<div class="text-danger p-3">Erro ao carregar dívidas.</div>');
    });
}

$(document).on('change', '#quitacaoCheckAll', function() {
    var checked = $(this).is(':checked');
    $('.quitacaoCheckbox:visible').prop('checked', checked).trigger('change');
});

// removed row-click and keyboard toggle to restore previous behavior

$(document).on('change', '.quitacaoCheckbox', function() {
    var total = 0;
    // consider only visible checkboxes (honors current search/filter)
    $('.quitacaoCheckbox:visible:checked').each(function() {
        total += parseFloat($(this).data('valor')) || 0;
    });
    $('#quitacaoTotal').text('R$ ' + total.toFixed(2));

    // update the "select all" checkbox to reflect visible selection state
    var totalVisible = $('.quitacaoCheckbox:visible').length;
    var checkedVisible = $('.quitacaoCheckbox:visible:checked').length;
    $('#quitacaoCheckAll').prop('checked', totalVisible > 0 && checkedVisible === totalVisible);
});

$(document).on('click', '#quitacaoPagarSelecionados', function(e) {
    e.preventDefault();
    var selecionados = [];
    var total = 0;
    $('#quitacaoDetalhe tbody tr').each(function(idx, tr) {
        var cb = $(tr).find('.quitacaoCheckbox');
        if (cb.is(':checked')) {
            var id = $(tr).data('id');
            var valor = parseFloat(cb.data('valor')) || 0;
            selecionados.push(id);
            total += valor;
        }
    });
    if (!selecionados.length) {
        showMessageModal('Atenção', 'Selecione ao menos um título para pagar.');
        return;
    }
    $('#quitacaoPagamentoIds').val(JSON.stringify(selecionados));
    $('#totalOriginal').val(total.toFixed(2));
    $('#quitacaoTotalSelecionado').text('R$ ' + total.toFixed(2));
    $('#quitacaoTotalRecebido').text('R$ ' + total.toFixed(2));
    $('#quitacaoTotalTroco').text('R$ 0,00');
    $('#quitacaoTotalRestante').text('R$ 0,00');
    $('#quitacaoObservacoes').val('');
    $('#quitacaoFormasList').empty();
    addQuitacaoForma({ metodo: 'dinheiro', valor: total.toFixed(2) });
    $('#quitacaoPagamentoModal').modal('show');
});

// Toggle contrato status from the listing (ajax)
$(document).on('change', '.contrato-status-switch', function() {
    var $cb = $(this);
    var id = $cb.data('id');
    var status = $cb.is(':checked') ? 'ativo' : 'inativo';
    $.post('/SystemContracts/contratos/toggle_status.php', { id: id, status: status }, function(resp) {
        try {
            var json = (typeof resp === 'object') ? resp : JSON.parse(resp);
            if (!json.success) {
                showMessageModal('Erro', json.message || 'Não foi possível atualizar status.');
                // revert checkbox
                $cb.prop('checked', !$cb.is(':checked'));
            }
        } catch (e) {
            showMessageModal('Erro', 'Resposta inválida do servidor.');
            $cb.prop('checked', !$cb.is(':checked'));
        }
    }).fail(function() {
        showMessageModal('Erro', 'Erro de rede ao atualizar status.');
        $cb.prop('checked', !$cb.is(':checked'));
    });
});

function addQuitacaoForma(data) {
    var template = document.getElementById('quitacaoFormaTemplate');
    var container = document.getElementById('quitacaoFormasList');
    if (!template || !container) return;

    var fragment = template.content.cloneNode(true);
    var item = fragment.querySelector('.quitacao-forma-item');
    var metodo = fragment.querySelector('.quitacao-forma-metodo');
    var valor = fragment.querySelector('.quitacao-forma-valor');
    var cartaoWrap = fragment.querySelector('.quitacao-forma-cartao-wrap');
    var cartaoTipo = fragment.querySelector('.quitacao-forma-cartao-tipo');

    if (data && data.metodo) metodo.value = data.metodo;
    if (data && data.valor !== undefined) valor.value = data.valor;
    if (data && data.cartao_tipo) cartaoTipo.value = data.cartao_tipo;

    container.appendChild(fragment);
    updateQuitacaoFormaVisibilidade(item);
    recalcQuitacaoSplit();
}

function updateQuitacaoFormaVisibilidade(item) {
    var $item = $(item);
    var metodo = $item.find('.quitacao-forma-metodo').val();
    if (metodo === 'cartao') {
        $item.find('.quitacao-forma-cartao-wrap').removeClass('d-none');
    } else {
        $item.find('.quitacao-forma-cartao-wrap').addClass('d-none');
    }
}

function recalcQuitacaoSplit() {
    var total = parseFloat($('#totalOriginal').val()) || 0;
    var recebido = 0;
    var temDinheiro = false;
    $('#quitacaoFormasList .quitacao-forma-item').each(function() {
        var $item = $(this);
        var valor = parseFloat($item.find('.quitacao-forma-valor').val()) || 0;
        var metodo = $item.find('.quitacao-forma-metodo').val();
        recebido += valor;
        if (metodo === 'dinheiro') {
            temDinheiro = true;
        }
    });
    var restante = total - recebido;
    var troco = (temDinheiro && recebido > total) ? (recebido - total) : 0;
    $('#quitacaoTotalSelecionado').text('R$ ' + total.toFixed(2));
    $('#quitacaoTotalRecebido').text('R$ ' + recebido.toFixed(2));
    $('#quitacaoTotalTroco').text('R$ ' + troco.toFixed(2));
    $('#quitacaoTotalRestante').text('R$ ' + Math.max(restante, 0).toFixed(2));

    var $restante = $('#quitacaoTotalRestante');
    var $troco = $('#quitacaoTotalTroco');

    $restante.toggleClass('text-danger', restante > 0);
    $restante.toggleClass('text-success', restante <= 0);
    $restante.toggleClass('text-warning', false);

    $troco.toggleClass('text-success', troco > 0);
    $troco.toggleClass('text-primary', troco <= 0);

    var $hint = $('#quitacaoTrocoHint');
    if ($hint.length) {
        if (recebido > total && !temDinheiro) {
            $hint.removeClass('alert-info alert-success').addClass('alert-danger').html('<i class="fas fa-exclamation-triangle mr-1"></i>Excesso sem dinheiro não gera troco. Ajuste os valores ou inclua uma forma em dinheiro.');
        } else if (troco > 0) {
            $hint.removeClass('alert-info alert-danger').addClass('alert-success').html('<i class="fas fa-check-circle mr-1"></i>Troco calculado automaticamente: <strong>R$ ' + troco.toFixed(2) + '</strong>.');
        } else {
            $hint.removeClass('alert-danger alert-success').addClass('alert-info').html('<i class="fas fa-info-circle mr-1"></i>Quando o valor em dinheiro for maior que o total, o troco é calculado automaticamente.');
        }
    }
}

$(document).on('click', '#addQuitacaoForma', function() {
    addQuitacaoForma({ metodo: 'dinheiro', valor: '' });
});

$(document).on('change', '.quitacao-forma-metodo', function() {
    updateQuitacaoFormaVisibilidade($(this).closest('.quitacao-forma-item')[0]);
    recalcQuitacaoSplit();
});

$(document).on('input', '.quitacao-forma-valor', function() {
    recalcQuitacaoSplit();
});

$(document).on('click', '.quitacao-remover-forma', function() {
    $(this).closest('.quitacao-forma-item').remove();
    if (!$('#quitacaoFormasList .quitacao-forma-item').length) {
        addQuitacaoForma({ metodo: 'dinheiro', valor: '' });
    }
    recalcQuitacaoSplit();
});

$(document).on('click', '#quitacaoConfirmarPagamento', function(e) {
    e.preventDefault();
    var ids = [];
    try {
        ids = JSON.parse($('#quitacaoPagamentoIds').val() || '[]');
    } catch (err) {
        ids = [];
    }
    if (!ids.length) {
        showMessageModal('Atenção', 'Selecione ao menos um título para pagar.');
        return;
    }
    var total = parseFloat($('#totalOriginal').val()) || 0;
    var formas = [];
    var valorPago = 0;
    var temDinheiro = false;

    $('#quitacaoFormasList .quitacao-forma-item').each(function() {
        var metodo = $(this).find('.quitacao-forma-metodo').val();
        var valor = parseFloat($(this).find('.quitacao-forma-valor').val()) || 0;
        var cartaoTipo = $(this).find('.quitacao-forma-cartao-tipo').val() || '';
        if (valor > 0) {
            valorPago += valor;
            formas.push({ metodo: metodo, valor: valor, cartao_tipo: cartaoTipo });
            if (metodo === 'dinheiro') {
                temDinheiro = true;
            }
        }
    });

    if (!formas.length) {
        showMessageModal('Atenção', 'Adicione ao menos uma forma de pagamento.');
        return;
    }

    if (valorPago < total - 0.01) {
        showMessageModal('Atenção', 'A soma das formas de pagamento precisa atingir o total da quitação.');
        return;
    }

    if (valorPago > total + 0.01 && !temDinheiro) {
        showMessageModal('Atenção', 'Troco só pode ser gerado quando houver pagamento em dinheiro.');
        return;
    }

    var observacoes = $('#quitacaoObservacoes').val();
    var $btn = $(this);
    $btn.prop('disabled', true).addClass('disabled');

    var request = $.post('/SystemContracts/pagamentos/quitacao_quitar.php', {
        ids: ids,
        valor_pago: valorPago,
        formas_json: JSON.stringify(formas),
        observacoes: observacoes
    }, function(resp) {
        if (!resp.success) {
            showMessageModal('Erro', resp.message || 'Erro ao processar pagamento.');
            return;
        }
        $('#quitacaoPagamentoModal').modal('hide');
        resp.updated.forEach(function(id) {
            $('#quitacaoDetalhe tbody tr[data-id="'+id+'"]').fadeOut(function(){ $(this).remove(); updateQuitacaoAfterRemoval(); });
        });
        abrirModalCupomQuitacao(resp);
    }, 'json');

    request.fail(function(){ showMessageModal('Erro', 'Erro na requisição.'); });
    request.always(function() {
        $btn.prop('disabled', false).removeClass('disabled');
    });
});

var ultimoCupomQuitacao = {
    previewUrl: '',
    pdfUrl: '',
    pdfUrlA4: '',
    pdfUrlThermal: '',
    imageUrl: ''
};

function abrirModalCupomQuitacao(resp) {
    var previewUrl = resp.pdf_url_termico || resp.pdf_url || resp.image_url;

    if (!previewUrl) {
        showMessageModal('Aviso', 'Quitação confirmada com sucesso, mas não foi possível abrir o cupom.');
        return;
    }

    ultimoCupomQuitacao.previewUrl = previewUrl;
    ultimoCupomQuitacao.pdfUrl = resp.pdf_url || resp.pdf_url_termico || '';
    ultimoCupomQuitacao.pdfUrlA4 = resp.pdf_url_a4 || resp.pdf_url || '';
    ultimoCupomQuitacao.pdfUrlThermal = resp.pdf_url_termico || resp.pdf_url || '';
    ultimoCupomQuitacao.imageUrl = resp.image_url || '';

    $('#quitacaoReciboPreview').attr('src', previewUrl);
    $('#quitacaoReciboModal').modal('show');
}

function showMessageModal(title, message) {
    var $modal = $('#genericMessageModal');
    if (!$modal.length) {
        var html = ''+
            '<div class="modal fade" id="genericMessageModal" tabindex="-1" role="dialog" aria-hidden="true">'+
            '  <div class="modal-dialog modal-dialog-centered" role="document">'+
            '    <div class="modal-content">'+
            '      <div class="modal-header">'+
            '        <h5 class="modal-title" id="genericMessageModalTitle">Mensagem</h5>'+
            '        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">'+
            '          <span aria-hidden="true">&times;</span>'+
            '        </button>'+
            '      </div>'+
            '      <div class="modal-body" id="genericMessageModalBody"></div>'+
            '      <div class="modal-footer">'+
            '        <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>'+
            '      </div>'+
            '    </div>'+
            '  </div>'+
            '</div>';
        $('body').append(html);
        $modal = $('#genericMessageModal');
    }
    $('#genericMessageModalTitle').text(title || 'Mensagem');
    $('#genericMessageModalBody').html(message || '');
    $modal.modal('show');
}

$(document).on('click', '#quitacaoReciboOpen', function() {
    if (!ultimoCupomQuitacao.previewUrl) return;
    window.open(ultimoCupomQuitacao.previewUrl, '_blank');
});

$(document).on('click', '#quitacaoReciboOpenA4', function() {
    var url = ultimoCupomQuitacao.pdfUrlA4 || ultimoCupomQuitacao.pdfUrl || ultimoCupomQuitacao.previewUrl;
    if (!url) return;
    window.open(url, '_blank');
});

$(document).on('click', '#quitacaoReciboOpenThermal', function() {
    var url = ultimoCupomQuitacao.pdfUrlThermal || ultimoCupomQuitacao.previewUrl || ultimoCupomQuitacao.pdfUrl;
    if (!url) return;
    window.open(url, '_blank');
});

$(document).on('click', '#quitacaoReciboPrint', function() {
    var iframe = document.getElementById('quitacaoReciboPreview');
    if (iframe && iframe.contentWindow) {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        return;
    }
    if (ultimoCupomQuitacao.previewUrl) {
        window.open(ultimoCupomQuitacao.previewUrl, '_blank');
    }
});

$(document).on('click', '#quitacaoReciboShare', function() {
    var shareTarget = ultimoCupomQuitacao.imageUrl || ultimoCupomQuitacao.pdfUrl || ultimoCupomQuitacao.previewUrl;
    if (!shareTarget) {
        showMessageModal('Aviso', 'Cupom indisponível para compartilhamento.');
        return;
    }

    if (navigator.share) {
        navigator.share({
            title: 'Cupom Fiscal - Quitação',
            text: 'Comprovante de quitação gerado com sucesso.',
            url: shareTarget
        }).catch(function(){});
        return;
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(shareTarget)
            .then(function(){ showMessageModal('Sucesso', 'Link do cupom copiado para a área de transferência.'); })
            .catch(function(){ window.open(shareTarget, '_blank'); });
        return;
    }

    window.open(shareTarget, '_blank');
});

function updateQuitacaoAfterRemoval() {
    var total = 0;
    $('#quitacaoDetalhe tbody tr').each(function(){
        var val = $(this).find('.quitacaoCheckbox').data('valor');
        if (val) total += parseFloat(val);
    });
    $('#quitacaoTotal').text('R$ ' + total.toFixed(2));
}

function escapeHtml(text) {
    return $('<div>').text(text).html();
}

// When the receipt modal is closed, refresh the clients list fragment without a full reload
$(document).on('hidden.bs.modal', '#quitacaoReciboModal', function() {
    // fetch current page and replace the clients list fragment
    $.get(window.location.href, function(html) {
        try {
            var $newList = $(html).find('#quitacaoClientesList');
            if ($newList.length) {
                $('#quitacaoClientesList').replaceWith($newList);
                // Ensure a client is selected and details are loaded
                var $first = $('.quitacao-cliente').first();
                if ($first.length) {
                    $('.quitacao-cliente').removeClass('active');
                    $first.addClass('active').trigger('click');
                } else {
                    $('#quitacaoDetalhe').html('<div class="text-center py-5 text-muted"><i class="fas fa-check-circle fa-3x mb-3 text-success"></i><br>Tudo em dia! Nenhuma pendência encontrada.</div>');
                }
            }
        } catch (e) {
            // if anything fails, fall back to a safe operation: reload current details
            var $active = $('.quitacao-cliente.active').first();
            if ($active.length) {
                loadQuitacaoDetalhes($active.data('client-id'));
            }
        }
    });
});
