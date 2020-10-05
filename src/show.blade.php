<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Order payment</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
</head>
<body>

	<div class="container text-center">
		<h1>Оплата услуги 100тг</h1>
		<!-- Button trigger modal -->
		<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#PayBox">
			Оплатить услугу
		</button>
		<div class="mt-4">
			Status: <span class="font-weight-bold" id="getStatus">в ожидании</span>
		</div>
	</div>

	<!-- Modal -->
	<div class="modal fade" id="PayBox" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog w-50">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Оплата услуги</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="d-flex justify-content-center">
						<div class="spinner-border" role="status">
							<span class="sr-only">Loading...</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>

	<script type="text/javascript">
		let PayBoxInit 		= "{{route('paybox')}}";
		let PayBoxStatus 	= "{{route('paybox.status')}}";
		let PayBoxStatusT 	= $('#getStatus');
		var PayBoxPaymentId = null;

		// После открытия модального окна, обращаемся к PayBoxInit
		$('.modal').on('shown.bs.modal',function(){
			$.get(PayBoxInit, function(data) {
				// Получаем идентификатор транзакции для дальнейшего получения статуса операции
				PayBoxPaymentId = data.pay_id;

				// Вставляем iframe в тело модального окна уже с ссылкой для iframe [pg_payment_route = frame]
				$('.modal-body').html('<iframe src="'+data.url+'" frameborder="0" style="width: 100%; min-height: 680px; height: 100%"></iframe>'); 
			});
		});

		// После закрытия модального окна проверяем статус платежа
		$('.modal').on('hidden.bs.modal',function() {
			// Даем знать о загрузке
			PayBoxStatusT.text('Загрузка...');

			// Обращаемся к PayBoxStatus
			$.get(PayBoxStatus, { PayId: PayBoxPaymentId}).done(function(data) {
				PayBoxStatusT.text(data.message +' №'+ data.pay_id);
			});
		});
	</script>
</body>
</html>