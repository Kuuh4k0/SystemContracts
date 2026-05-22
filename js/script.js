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
            alert('CEP não encontrado ou inválido.');
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

// Quitação: load debts and handle payments
$(document).on('click', '.quitacao-cliente', function(e) {
    e.preventDefault();
    var clienteId = $(this).data('client-id');
    $('.quitacao-cliente').removeClass('active');
    $(this).addClass('active');
    loadQuitacaoDetalhes(clienteId);
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
            $('#quitacaoDetalhe').html('<div class="p-3 text-muted">Nenhuma dívida encontrada para este cliente.</div>');
            return;
        }
        var html = [];
        html.push('<div class="d-flex justify-content-between align-items-center mb-2">');
        html.push('<h5 class="mb-0">Dívidas</h5>');
        html.push('<div><button class="btn btn-sm btn-primary" id="quitacaoPagarSelecionados">Registrar pagamento</button></div>');
        html.push('</div>');
        html.push('<div class="table-responsive"><table class="table table-sm table-hover">');
        html.push('<thead><tr><th><input type="checkbox" id="quitacaoCheckAll"></th><th>Descrição</th><th>Vencimento</th><th class="text-right">Valor</th><th>Situação</th></tr></thead>');
        html.push('<tbody>');
        items.forEach(function(it) {
            html.push('<tr data-id="'+it.id+'">');
            html.push('<td><input type="checkbox" class="quitacaoCheckbox" data-valor="'+it.valor+'"></td>');
            html.push('<td>'+escapeHtml(it.descricao)+'</td>');
            html.push('<td>'+it.vencimento+'</td>');
            html.push('<td class="text-right">R$ '+parseFloat(it.valor).toFixed(2)+'</td>');
            html.push('<td>'+escapeHtml(it.situacao)+'</td>');
            html.push('</tr>');
        });
        html.push('</tbody></table></div>');
        html.push('<div class="d-flex justify-content-between align-items-center">');
        html.push('<div>Pagamento em: <select id="quitacaoMetodo" class="form-control form-control-sm d-inline-block" style="width:auto;"><option value="dinheiro">Dinheiro</option><option value="cartao">Cartão</option><option value="pix">PIX</option></select></div>');
        html.push('<div class="font-weight-bold">Total: <span id="quitacaoTotal">R$ 0.00</span></div>');
        html.push('</div>');

        $('#quitacaoDetalhe').html(html.join(''));
    }).fail(function() {
        $('#quitacaoDetalhe').html('<div class="text-danger p-3">Erro ao carregar dívidas.</div>');
    });
}

$(document).on('change', '#quitacaoCheckAll', function() {
    var checked = $(this).is(':checked');
    $('.quitacaoCheckbox').prop('checked', checked).trigger('change');
});

$(document).on('change', '.quitacaoCheckbox', function() {
    var total = 0;
    $('.quitacaoCheckbox:checked').each(function() {
        total += parseFloat($(this).data('valor')) || 0;
    });
    $('#quitacaoTotal').text('R$ ' + total.toFixed(2));
});

$(document).on('click', '#quitacaoPagarSelecionados', function(e) {
    e.preventDefault();
    var ids = [];
    $('#quitacaoDetalhe tbody tr').each(function(idx, tr) {
        var cb = $(tr).find('.quitacaoCheckbox');
        if (cb.is(':checked')) {
            ids.push($(tr).data('id'));
        }
    });
    if (!ids.length) {
        alert('Selecione ao menos um título para pagar.');
        return;
    }
    var metodo = $('#quitacaoMetodo').val();
    var observacoes = prompt('Observações (opcional):', '');
    $.post('/SystemContracts/pagamentos/quitacao_quitar.php', { ids: ids, metodo: metodo, observacoes: observacoes }, function(resp) {
        if (!resp.success) {
            alert(resp.message || 'Erro ao processar pagamento.');
            return;
        }
        resp.updated.forEach(function(id) {
            $('#quitacaoDetalhe tbody tr[data-id="'+id+'"]').fadeOut(function(){ $(this).remove(); updateQuitacaoAfterRemoval(); });
        });
    }, 'json').fail(function(){ alert('Erro na requisição.'); });
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

